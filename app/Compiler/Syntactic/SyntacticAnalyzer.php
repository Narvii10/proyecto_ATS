<?php

namespace App\Compiler\Syntactic;

use App\Compiler\Lexical\SymbolTable;
use App\Compiler\Lexical\Token;

class SyntacticAnalyzer
{
    private const REQUIRED_SECTIONS = ['PersonalData', 'Education', 'Experience', 'Skills'];
    private const OPTIONAL_SECTIONS = ['Contact'];
    private const EXPECTED_ORDER    = ['PersonalData', 'Contact', 'Education', 'Experience', 'Skills'];

    private const SECTION_KEYWORDS = [
        'PersonalData' => ['datos personales', 'personal info', 'perfil', 'información personal', 'informacion personal'],
        'Contact'      => ['contacto', 'contact', 'información de contacto', 'informacion de contacto'],
        'Education'    => ['educación', 'education', 'formación académica', 'estudios', 'formacion academica'],
        'Experience'   => ['experiencia', 'experience', 'experiencia laboral', 'experiencia profesional'],
        'Skills'       => ['habilidades', 'skills', 'competencias', 'tecnologías', 'tecnologias'],
    ];

    /**
     * Punto de entrada del análisis sintáctico.
     * Verifica que el CV tenga las secciones requeridas, en el orden correcto y sin duplicados.
     * @return array{tree: ParseNode, errors: SyntacticError[]}
     */
    public function analyze(string $text, SymbolTable $symbolTable): array
    {
        $lines  = explode("\n", $text);
        $errors = [];

        $detectedSections = $this->detectSections($lines);
        $root             = $this->buildParseTree($text, $lines, $detectedSections, $symbolTable);

        $this->validateRequiredSections($detectedSections, $errors);
        $this->validateOrder($detectedSections, $errors);
        $this->validateDuplicates($detectedSections, $errors);
        $this->validateSectionContent($lines, $detectedSections, $errors, $symbolTable);

        return ['tree' => $root, 'errors' => $errors];
    }

    /**
     * Detecta qué secciones están presentes en el CV y en qué líneas aparecen.
     * @return array<string, int[]> sección => [números de línea]
     */
    private function detectSections(array $lines): array
    {
        $found = [];

        foreach ($lines as $i => $line) {
            $lower = mb_strtolower(trim($line));
            foreach (self::SECTION_KEYWORDS as $section => $keywords) {
                foreach ($keywords as $keyword) {
                    if (str_contains($lower, $keyword)) {
                        $found[$section][] = $i + 1;
                        break;
                    }
                }
            }
        }

        return $found;
    }

    private function buildParseTree(string $text, array $lines, array $sections, SymbolTable $st): ParseNode
    {
        $root = new ParseNode('CV', null, 0);

        foreach (self::EXPECTED_ORDER as $sectionName) {
            if (!isset($sections[$sectionName])) {
                continue;
            }

            $sectionNode = new ParseNode($sectionName, null, $sections[$sectionName][0]);

            $tokensForSection = $this->getTokensForSection($sectionName, $st);
            foreach ($tokensForSection as $token) {
                $sectionNode->addChild(new ParseNode($token->type, $token->value, $token->line));
            }

            $root->addChild($sectionNode);
        }

        return $root;
    }

    /**
     * Devuelve los tokens léxicos que corresponden a cada sección del árbol de análisis.
     * @return Token[]
     */
    private function getTokensForSection(string $section, SymbolTable $st): array
    {
        $map = [
            'PersonalData' => [Token::TOKEN_NAME, Token::TOKEN_EMAIL, Token::TOKEN_PHONE, Token::TOKEN_AGE],
            'Contact'      => [Token::TOKEN_EMAIL, Token::TOKEN_PHONE],
            'Education'    => [Token::TOKEN_UNIVERSITY, Token::TOKEN_CAREER, Token::TOKEN_DATE],
            'Experience'   => [Token::TOKEN_EXPERIENCE_YEARS, Token::TOKEN_DATE],
            'Skills'       => [Token::TOKEN_SKILL, Token::TOKEN_LANGUAGE, Token::TOKEN_CERTIFICATION],
        ];

        $result = [];
        foreach ($map[$section] ?? [] as $type) {
            foreach ($st->byType($type) as $token) {
                $result[] = $token;
            }
        }

        return $result;
    }

    private function validateRequiredSections(array $found, array &$errors): void
    {
        foreach (self::REQUIRED_SECTIONS as $section) {
            if (!isset($found[$section])) {
                $errors[] = new SyntacticError(
                    'MISSING_SECTION',
                    $section,
                    null,
                    "Sección requerida ausente: '$section'"
                );
            }
        }
    }

    private function validateOrder(array $found, array &$errors): void
    {
        $presentOrder = [];
        foreach (self::EXPECTED_ORDER as $section) {
            if (isset($found[$section])) {
                $presentOrder[] = ['section' => $section, 'line' => min($found[$section])];
            }
        }

        for ($i = 0; $i < count($presentOrder) - 1; $i++) {
            if ($presentOrder[$i]['line'] > $presentOrder[$i + 1]['line']) {
                $errors[] = new SyntacticError(
                    'WRONG_ORDER',
                    $presentOrder[$i]['section'],
                    $presentOrder[$i]['line'],
                    "Sección '{$presentOrder[$i]['section']}' aparece después de '{$presentOrder[$i+1]['section']}'"
                );
            }
        }
    }

    private function validateDuplicates(array $found, array &$errors): void
    {
        foreach ($found as $section => $lines) {
            if (count($lines) > 1) {
                $errors[] = new SyntacticError(
                    'DUPLICATE_SECTION',
                    $section,
                    $lines[1],
                    "Sección duplicada: '$section' aparece " . count($lines) . ' veces.'
                );
            }
        }
    }

    private function validateSectionContent(array $lines, array $found, array &$errors, SymbolTable $st): void
    {
        foreach ($found as $section => $sectionLines) {
            $sectionLine = $sectionLines[0];

            // SECCIÓN_VACÍA — revisa las siguientes 5 líneas en busca de contenido
            $hasContent = false;
            for ($i = $sectionLine; $i < min($sectionLine + 5, count($lines)); $i++) {
                if (trim($lines[$i] ?? '') !== '') {
                    $hasContent = true;
                    break;
                }
            }
            if (!$hasContent) {
                $errors[] = new SyntacticError(
                    'EMPTY_SECTION',
                    $section,
                    $sectionLine,
                    "Sección '$section' encontrada pero sin contenido."
                );
            }

            // ENTRADA_INCOMPLETA para Educación — requiere universidad Y carrera
            if ($section === 'Education') {
                $hasUniversity = count($st->byType(Token::TOKEN_UNIVERSITY)) > 0;
                $hasCareer     = count($st->byType(Token::TOKEN_CAREER)) > 0;
                if (!$hasUniversity || !$hasCareer) {
                    $missing = [];
                    if (!$hasUniversity) {
                        $missing[] = 'universidad';
                    }
                    if (!$hasCareer) {
                        $missing[] = 'carrera/degree';
                    }
                    $errors[] = new SyntacticError(
                        'INCOMPLETE_ENTRY',
                        'Education',
                        $sectionLine,
                        'Entrada educativa incompleta, falta: ' . implode(', ', $missing)
                    );
                }
            }

            // ENTRADA_INCOMPLETA para Experiencia — al menos debe haber fechas de empleo
            if ($section === 'Experience') {
                $hasDates = count($st->byType(Token::TOKEN_DATE)) > 0;
                if (!$hasDates) {
                    $errors[] = new SyntacticError(
                        'INCOMPLETE_ENTRY',
                        'Experience',
                        $sectionLine,
                        'Entrada de experiencia incompleta: no se encontraron fechas de empleo.'
                    );
                }
            }
        }
    }
}
