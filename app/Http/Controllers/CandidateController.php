<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Services\AIClassifierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function index(Request $request): View
    {
        $search   = $request->input('search');
        $minScore = $request->input('min_score');
        $status   = $request->input('status');

        $candidates = Candidate::with(['cvDocuments', 'compatibilityResults'])
            ->withCount('cvDocuments')
            ->search($search)
            ->when($minScore, fn($q) =>
                $q->whereHas('compatibilityResults', fn($r) =>
                    $r->where('total_score', '>=', (float) $minScore)
                )
            )
            ->when($status, fn($q) =>
                $q->whereHas('cvDocuments', fn($d) =>
                    $d->where('processing_status', $status)
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('candidates.index', compact('candidates', 'search', 'minScore', 'status'));
    }

    public function show(Candidate $candidate): View
    {
        $candidate->load([
            'cvDocuments.lexicalTokens',
            'cvDocuments.lexicalErrors',
            'cvDocuments.parseTree',
            'cvDocuments.syntacticErrors',
            'cvDocuments.semanticErrors',
            'compatibilityResults.vacancy',
            'notes',
        ]);

        $latestCv = $candidate->cvDocuments->first();

        return view('candidates.show', compact('candidate', 'latestCv'));
    }

    public function serveCV(Candidate $candidate): Response|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $cv = $candidate->cvDocuments()->latest()->first();

        abort_if(!$cv || !$cv->file_path, 404);

        $path = $cv->file_path;

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Archivo no encontrado.');
        }

        $mimeMap = [
            'pdf'  => 'application/pdf',
            'txt'  => 'text/plain; charset=utf-8',
            'json' => 'application/json',
            'xml'  => 'application/xml',
        ];

        $mime = $mimeMap[$cv->format] ?? 'application/octet-stream';

        return Storage::disk('local')->response(
            $path,
            $cv->original_filename,
            ['Content-Type' => $mime, 'Content-Disposition' => 'inline']
        );
    }

    public function reanalyze(Candidate $candidate): RedirectResponse
    {
        $cv = $candidate->cvDocuments()->latest()->first();

        if (!$cv) {
            return back()->with('error', 'No hay CV para analizar.');
        }

        $text = $cv->raw_content ?? '';

        $ai = new AIClassifierService();
        $ok = $ai->classify($candidate, $text);

        return back()->with(
            $ok ? 'success' : 'error',
            $ok ? 'Análisis IA completado.' : 'No se pudo conectar con Groq. Verifica tu GROQ_API_KEY.'
        );
    }

    public function destroy(Candidate $candidate): RedirectResponse
    {
        $candidate->delete();
        return redirect()->route('candidates.index')
            ->with('success', 'Candidato eliminado.');
    }
}
