<?php

namespace App\Http\Controllers;

use App\Compiler\AST\ASTBuilder;
use App\Compiler\Lexical\LexicalAnalyzer;
use App\Compiler\Lexical\Token;
use App\Compiler\Semantic\SemanticAnalyzer;
use App\Compiler\Syntactic\SyntacticAnalyzer;
use App\Models\Candidate;
use App\Models\CvDocument;
use App\Models\LexicalToken;
use App\Models\ParseTree;
use App\Models\Vacancy;
use App\Services\AIClassifierService;
use App\Services\CompatibilityEngine;
use App\Services\CVParserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CVUploadController extends Controller
{
    public function create(): View
    {
        $vacancies = Vacancy::orderBy('title')->get();
        return view('candidates.upload', compact('vacancies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'cv_file'    => 'required|file|mimes:txt,json,xml,pdf|max:10240',
            'vacancy_id' => 'nullable|exists:vacancies,id',
        ]);

        $file   = $request->file('cv_file');
        $format = strtolower($file->getClientOriginalExtension());

        // Store file
        $path = $file->store('cv_uploads', 'local');

        // Parse raw content
        $parser = new CVParserService();
        $parsed = $parser->parse($file);
        $text   = $parsed['text'] ?? '';

        // Create CV document (no candidate yet)
        $cvDoc = CvDocument::create([
            'candidate_id'     => null,
            'original_filename'=> $file->getClientOriginalName(),
            'format'           => $format,
            'file_path'        => $path,
            'raw_content'      => $text,
            'parsed_content'   => $parsed,
            'processing_status'=> 'processing',
        ]);

        // ── Phase 2: Lexical analysis ──────────────────────────────────
        $lexer    = new LexicalAnalyzer();
        $lexResult = $lexer->analyze($text);
        /** @var \App\Compiler\Lexical\SymbolTable $symbolTable */
        $symbolTable = $lexResult['symbolTable'];
        $lexErrors   = $lexResult['errors'];

        // Persist tokens
        foreach ($symbolTable->all() as $token) {
            LexicalToken::create([
                'cv_document_id' => $cvDoc->id,
                'type'           => $token->type,
                'value'          => $token->value,
                'line'           => $token->line,
                'position'       => $token->position,
            ]);
        }

        // Persist lexical errors
        foreach ($lexErrors as $err) {
            \App\Models\LexicalError::create([
                'cv_document_id' => $cvDoc->id,
                'code'           => $err->code,
                'value'          => $err->value,
                'line'           => $err->line,
                'message'        => $err->message,
            ]);
        }

        // Create or update Candidate from extracted tokens
        $nameToken  = $symbolTable->firstOfType(Token::TOKEN_NAME);
        $emailToken = $symbolTable->firstOfType(Token::TOKEN_EMAIL);
        $phoneToken = $symbolTable->firstOfType(Token::TOKEN_PHONE);
        $ageToken   = $symbolTable->firstOfType(Token::TOKEN_AGE);

        // Duplicate detection — same email already in DB
        if ($emailToken?->value) {
            $existing = Candidate::where('email', $emailToken->value)->first();
            if ($existing) {
                $cvDoc->delete();
                return redirect()->route('candidates.show', $existing)
                    ->with('warning', "Ya existe un candidato con el correo {$emailToken->value}. Se muestra el perfil existente.");
            }
        }

        $candidate = Candidate::create([
            'name'       => $nameToken?->value,
            'email'      => $emailToken?->value,
            'phone'      => $phoneToken?->value,
            'age'        => $ageToken ? (int) $ageToken->value : null,
            'vacancy_id' => $request->input('vacancy_id'),
        ]);

        $cvDoc->update(['candidate_id' => $candidate->id]);

        // ── Phase 3: Syntactic analysis ───────────────────────────────
        $syntactic  = new SyntacticAnalyzer();
        $synResult  = $syntactic->analyze($text, $symbolTable);
        $parseTree  = $synResult['tree'];
        $synErrors  = $synResult['errors'];

        foreach ($synErrors as $err) {
            \App\Models\SyntacticError::create([
                'cv_document_id' => $cvDoc->id,
                'code'           => $err->code,
                'section'        => $err->section,
                'line'           => $err->line,
                'message'        => $err->message,
            ]);
        }

        // ── Phase 4: AST ──────────────────────────────────────────────
        $astBuilder = new ASTBuilder();
        $ast        = $astBuilder->build($parseTree, $symbolTable);

        ParseTree::create([
            'cv_document_id' => $cvDoc->id,
            'tree_json'      => json_encode($parseTree->toArray()),
            'ast_json'       => json_encode($ast->toArray()),
        ]);

        // ── Phase 5: Semantic analysis ────────────────────────────────
        $semantic   = new SemanticAnalyzer();
        $semErrors  = $semantic->analyze($ast, $symbolTable);

        foreach ($semErrors as $err) {
            \App\Models\SemanticError::create([
                'cv_document_id' => $cvDoc->id,
                'code'           => $err->code,
                'field'          => $err->field,
                'severity'       => $err->severity,
                'message'        => $err->message,
                'suggestion'     => $err->suggestion,
            ]);
        }

        // Fallback: derive experience years from date ranges when no explicit token was found
        if (!$symbolTable->firstOfType(Token::TOKEN_EXPERIENCE_YEARS)) {
            $lines       = array_merge($parsed['lines'] ?? [], explode("\n", $text));
            $datePattern = '/\b(\d{4})\s*[-–—]\s*(\d{4}|presente|present|actual|hoy)\b/i';
            $totalMonths = 0;
            $seen        = [];
            foreach ($lines as $line) {
                if (!preg_match($datePattern, $line, $m)) {
                    continue;
                }
                $startYear = (int) $m[1];
                $endRaw    = strtolower(trim($m[2]));
                $endYear   = in_array($endRaw, ['presente', 'present', 'actual', 'hoy'])
                    ? (int) date('Y')
                    : (int) $m[2];
                $key = "$startYear-$endYear";
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                if ($endYear >= $startYear && $startYear >= 1970 && $endYear <= (int) date('Y') + 1) {
                    $totalMonths += ($endYear - $startYear) * 12;
                }
            }
            if ($totalMonths > 0) {
                $years = max(1, (int) round($totalMonths / 12));
                $symbolTable->add(new Token(Token::TOKEN_EXPERIENCE_YEARS, (string) $years, 0, 0));
                LexicalToken::create([
                    'cv_document_id' => $cvDoc->id,
                    'type'           => Token::TOKEN_EXPERIENCE_YEARS,
                    'value'          => (string) $years,
                    'line'           => 0,
                    'position'       => 0,
                ]);
            }
        }

        // ── Phase 6: Compatibility scoring ────────────────────────────
        $engine = new CompatibilityEngine();
        $engine->scoreAll($candidate, $symbolTable, $text);

        // ── Phase 7: AI Classification ────────────────────────────────
        $ai = new AIClassifierService();
        $ai->classify($candidate, $text);

        $cvDoc->update(['processing_status' => 'completed']);

        return redirect()->route('candidates.show', $candidate->id)
            ->with('success', 'CV procesado exitosamente.')
            ->with('highlight_vacancy', $request->input('vacancy_id'));
    }
}
