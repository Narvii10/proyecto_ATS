<?php

namespace App\Services;

use App\Compiler\Lexical\SymbolTable;
use App\Compiler\Lexical\Token;
use App\Models\Candidate;
use App\Models\CompatibilityResult;
use App\Models\Vacancy;

class CompatibilityEngine
{
    private const ALIASES = [
        'nodejs'          => 'node.js',
        'node js'         => 'node.js',
        'node'            => 'node.js',
        'reactjs'         => 'react',
        'react.js'        => 'react',
        'vuejs'           => 'vue',
        'vue.js'          => 'vue',
        'vue3'            => 'vue',
        'vue 3'           => 'vue',
        'nextjs'          => 'next.js',
        'nuxtjs'          => 'nuxt.js',
        'angularjs'       => 'angular',
        'springboot'      => 'spring boot',
        'spring-boot'     => 'spring boot',
        'k8s'             => 'kubernetes',
        'mongo'           => 'mongodb',
        'postgres'        => 'postgresql',
        'tailwindcss'     => 'tailwind',
        'tailwind css'    => 'tailwind',
        'js'              => 'javascript',
        'ts'              => 'typescript',
        'golang'          => 'go',
        'python3'         => 'python',
        'rest api'        => 'rest',
        'restful'         => 'rest',
        'restful api'     => 'rest',
        'ci cd'           => 'ci/cd',
        'github'          => 'git',
        'gitlab'          => 'git',
        'ml'              => 'machine learning',
        'dl'              => 'deep learning',
        'laravel 10'      => 'laravel',
        'laravel 11'      => 'laravel',
        'symfony 6'       => 'symfony',
        'react native'    => 'react native',
    ];

    private const EDUCATION_LEVELS = [
        'any'         => 0,
        'bachillerato'=> 1,
        'tecnico'     => 2,
        'licenciatura'=> 3,
        'ingenieria'  => 3,
        'maestria'    => 4,
        'doctorado'   => 5,
    ];

    private function normalize(string $skill): string
    {
        $lower = mb_strtolower(trim($skill));
        return self::ALIASES[$lower] ?? $lower;
    }

    public function scoreAll(Candidate $candidate, SymbolTable $symbolTable, string $rawCvText = ''): array
    {
        $results = [];

        foreach (Vacancy::all() as $vacancy) {
            $result    = $this->score($candidate, $vacancy, $symbolTable, $rawCvText);
            $results[] = $result;
        }

        $this->updateRanking($results);

        return $results;
    }

    public function score(Candidate $candidate, Vacancy $vacancy, SymbolTable $symbolTable, string $rawCvText = ''): array
    {
        // ── Intento con IA (Groq) si hay clave configurada ──────────────────
        if ($rawCvText && !empty(config('services.groq.key'))) {
            $aiScores = $this->scoreWithGroq($vacancy, $rawCvText);
            if ($aiScores) {
                return $this->persist($candidate, $vacancy, $aiScores);
            }
        }

        // ── Fallback: comparación por coincidencia de strings ────────────────
        return $this->scoreWithStringMatching($candidate, $vacancy, $symbolTable);
    }

    // ────────────────────────────────────────────────────────────────────────
    // AI SCORING (Groq)
    // ────────────────────────────────────────────────────────────────────────
    private function scoreWithGroq(Vacancy $vacancy, string $rawCvText): ?array
    {
        try {
            $skills = implode(', ', $vacancy->required_skills ?? []) ?: 'No especificadas';
            $langs  = implode(', ', $vacancy->required_languages ?? []) ?: 'No especificados';
            $certs  = implode(', ', $vacancy->preferred_certifications ?? []) ?: 'Ninguna';
            $edu    = $vacancy->required_education_level ?? 'any';
            $years  = $vacancy->required_years_experience ?? 0;

            // Limitar el CV a 2500 caracteres para no superar el límite de tokens de Groq
            $cvText = mb_substr($rawCvText, 0, 2500);

            $prompt = <<<PROMPT
Eres un evaluador de Recursos Humanos. Analiza la compatibilidad entre este CV y la vacante.

VACANTE:
- Título: {$vacancy->title}
- Descripción: {$vacancy->description}
- Habilidades técnicas requeridas: {$skills}
- Lenguajes/tecnologías requeridos: {$langs}
- Años de experiencia mínimos: {$years}
- Nivel educativo requerido: {$edu}
- Certificaciones preferidas: {$certs}

CV DEL CANDIDATO:
{$cvText}

Responde ÚNICAMENTE con JSON válido (sin markdown, sin texto extra):
{"skills_score":0,"languages_score":0,"experience_score":0,"education_score":0,"certifications_score":0,"total_score":0,"matched":[],"missing":[]}

Instrucciones para cada campo:
- skills_score: % de habilidades técnicas requeridas que el candidato cumple (0-100). Si no hay habilidades requeridas, pon 100.
- languages_score: % de lenguajes/tecnologías requeridos que cumple. Si no hay, pon 100.
- experience_score: 100 si cumple los años requeridos, proporcional si está cerca (75% si tiene ≥75% de los años, 50% si ≥50%, 0 si menos). Suma años de distintos trabajos si el CV los lista por separado.
- education_score: 100 si el nivel educativo del candidato es igual o mayor al requerido, 0 si no.
- certifications_score: % de certificaciones preferidas que tiene. Si no hay preferidas, pon 100.
- total_score: evaluación holística 0-100 considerando todo el contexto, habilidades relacionadas/equivalentes y experiencia real aunque no sea exacta. NO es un simple promedio.
- matched: lista de elementos de la vacante que el candidato cumple.
- missing: lista de elementos requeridos que le faltan al candidato.
PROMPT;

            $client = new \GuzzleHttp\Client(['timeout' => 20]);

            $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.groq.key'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => 'llama-3.3-70b-versatile',
                    'max_tokens'  => 600,
                    'temperature' => 0.1,
                    'messages'    => [
                        ['role' => 'system', 'content' => 'Eres un evaluador de RRHH. Responde solo con JSON válido, sin markdown ni explicaciones.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $text = $body['choices'][0]['message']['content'] ?? '';
            $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
            $text = preg_replace('/\s*```$/m',          '', $text);
            $data = json_decode(trim($text), true);

            if (!is_array($data) || !isset($data['total_score'])) {
                return null;
            }

            return [
                'skills_score'        => (float) ($data['skills_score']         ?? 100),
                'languages_score'     => (float) ($data['languages_score']      ?? 100),
                'experience_score'    => (float) ($data['experience_score']     ?? 100),
                'education_score'     => (float) ($data['education_score']      ?? 100),
                'certifications_score'=> (float) ($data['certifications_score'] ?? 100),
                'total_score'         => min(100, max(0, (float) $data['total_score'])),
                'matched'             => array_values((array) ($data['matched'] ?? [])),
                'missing'             => array_values((array) ($data['missing'] ?? [])),
            ];

        } catch (\Exception $e) {
            \Log::warning('CompatibilityEngine Groq failed: ' . $e->getMessage());
            return null;
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // STRING MATCHING FALLBACK
    // ────────────────────────────────────────────────────────────────────────
    private function scoreWithStringMatching(Candidate $candidate, Vacancy $vacancy, SymbolTable $symbolTable): array
    {
        // Normalizar los requisitos de la vacante (aplicar aliases y minúsculas)
        $requiredSkills = array_values(array_unique(array_map(fn($s) => $this->normalize($s), $vacancy->required_skills   ?? [])));
        $requiredLangs  = array_values(array_unique(array_map(fn($s) => $this->normalize($s), $vacancy->required_languages ?? [])));
        $preferredCerts = array_values(array_unique(array_map('mb_strtolower', $vacancy->preferred_certifications ?? [])));
        $requiredYears  = (int) $vacancy->required_years_experience;
        $requiredEdu    = mb_strtolower($vacancy->required_education_level ?? 'any');

        // Combinar TOKEN_SKILL + TOKEN_LANGUAGE del candidato para evitar inconsistencias
        // (ej: PHP puede aparecer como lenguaje o como habilidad según el CV y la vacante)
        $allCandidateTech = array_values(array_unique(array_map(
            fn(Token $t) => $this->normalize($t->value),
            array_merge(
                $symbolTable->byType(Token::TOKEN_SKILL),
                $symbolTable->byType(Token::TOKEN_LANGUAGE)
            )
        )));

        $candidateCerts = array_values(array_unique(array_map(
            'mb_strtolower',
            array_map(fn(Token $t) => $t->value, $symbolTable->byType(Token::TOKEN_CERTIFICATION))
        )));

        $expToken      = $symbolTable->firstOfType(Token::TOKEN_EXPERIENCE_YEARS);
        $candidateYears= $expToken ? (int) $expToken->value : 0;
        $careerToken   = $symbolTable->firstOfType(Token::TOKEN_CAREER);
        $candidateEdu  = $careerToken ? mb_strtolower($careerToken->value) : '';

        // Skills match (vs combined tech pool)
        $matchedSkills = array_intersect($allCandidateTech, $requiredSkills);
        $missingSkills = array_diff($requiredSkills, $allCandidateTech);
        $skillsScore   = !empty($requiredSkills) ? (count($matchedSkills) / count($requiredSkills)) * 100 : null;

        // Languages match (vs combined tech pool)
        $matchedLangs  = array_intersect($allCandidateTech, $requiredLangs);
        $missingLangs  = array_diff($requiredLangs, $allCandidateTech);
        $langsScore    = !empty($requiredLangs) ? (count($matchedLangs) / count($requiredLangs)) * 100 : null;

        // Experience & education (always scored)
        $expScore = $this->calcExperienceScore($candidateYears, $requiredYears);
        $eduScore = $this->calcEducationScore($candidateEdu, $requiredEdu);

        // Certifications
        $matchedCerts = array_intersect($candidateCerts, $preferredCerts);
        $missingCerts = array_diff($preferredCerts, $candidateCerts);
        $certsScore   = !empty($preferredCerts) ? (count($matchedCerts) / count($preferredCerts)) * 100 : null;

        // Puntaje ponderado — solo se incluyen dimensiones con requisitos reales.
        // Si la vacante no exige algo (ej: certificaciones), ese peso se redistribuye.
        $rawWeights = [
            'skills'         => $skillsScore !== null ? 0.30 : 0,
            'languages'      => $langsScore  !== null ? 0.25 : 0,
            'experience'     => 0.20,
            'education'      => 0.15,
            'certifications' => $certsScore  !== null ? 0.10 : 0,
        ];
        $weightSum  = array_sum($rawWeights);
        $totalScore = 0.0;

        if ($weightSum > 0) {
            $totalScore = (
                (($skillsScore ?? 0) * $rawWeights['skills']) +
                (($langsScore  ?? 0) * $rawWeights['languages']) +
                ($expScore           * $rawWeights['experience']) +
                ($eduScore           * $rawWeights['education']) +
                (($certsScore  ?? 0) * $rawWeights['certifications'])
            ) / $weightSum;
        }

        $matched = array_values(array_merge(
            array_map(fn($s) => "Skill: $s",    array_values($matchedSkills)),
            array_map(fn($l) => "Language: $l", array_values($matchedLangs)),
            array_map(fn($c) => "Cert: $c",     array_values($matchedCerts)),
        ));
        $missing = array_values(array_merge(
            array_map(fn($s) => "Skill: $s",    array_values($missingSkills)),
            array_map(fn($l) => "Language: $l", array_values($missingLangs)),
            array_map(fn($c) => "Cert: $c",     array_values($missingCerts)),
        ));

        return $this->persist($candidate, $vacancy, [
            'skills_score'        => round($skillsScore ?? 100, 2),
            'languages_score'     => round($langsScore  ?? 100, 2),
            'experience_score'    => round($expScore,    2),
            'education_score'     => round($eduScore,    2),
            'certifications_score'=> round($certsScore  ?? 100, 2),
            'total_score'         => round($totalScore,  2),
            'matched'             => $matched,
            'missing'             => $missing,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // SHARED HELPERS
    // ────────────────────────────────────────────────────────────────────────
    private function persist(Candidate $candidate, Vacancy $vacancy, array $scores): array
    {
        CompatibilityResult::updateOrCreate(
            ['candidate_id' => $candidate->id, 'vacancy_id' => $vacancy->id],
            [
                'total_score'         => $scores['total_score'],
                'skills_score'        => $scores['skills_score'],
                'languages_score'     => $scores['languages_score'],
                'experience_score'    => $scores['experience_score'],
                'education_score'     => $scores['education_score'],
                'certifications_score'=> $scores['certifications_score'],
                'matched'             => $scores['matched'],
                'missing'             => $scores['missing'],
            ]
        );

        return [
            'candidate_id' => $candidate->id,
            'vacancy_id'   => $vacancy->id,
            'total_score'  => $scores['total_score'],
            'breakdown'    => [
                'skills'        => $scores['skills_score'],
                'languages'     => $scores['languages_score'],
                'experience'    => $scores['experience_score'],
                'education'     => $scores['education_score'],
                'certifications'=> $scores['certifications_score'],
            ],
            'matched' => $scores['matched'],
            'missing' => $scores['missing'],
            'rank'    => null,
        ];
    }

    private function calcExperienceScore(int $candidate, int $required): float
    {
        if ($required === 0) return 100.0;
        if ($candidate >= $required)        return 100.0;
        if ($candidate >= $required * 0.75) return 75.0;
        if ($candidate >= $required * 0.50) return 50.0;
        return 0.0;
    }

    private function calcEducationScore(string $candidate, string $required): float
    {
        if ($required === 'any') return 100.0;

        $candidateLevel = 0;
        $requiredLevel  = self::EDUCATION_LEVELS[$required] ?? 0;

        foreach (self::EDUCATION_LEVELS as $keyword => $level) {
            if (str_contains($candidate, $keyword)) {
                $candidateLevel = max($candidateLevel, $level);
            }
        }

        return $candidateLevel >= $requiredLevel ? 100.0 : 0.0;
    }

    private function updateRanking(array &$results): void
    {
        usort($results, fn($a, $b) => $b['total_score'] <=> $a['total_score']);

        foreach ($results as $rank => &$result) {
            $result['rank'] = $rank + 1;
            CompatibilityResult::where('candidate_id', $result['candidate_id'])
                ->where('vacancy_id', $result['vacancy_id'])
                ->update(['rank' => $rank + 1]);
        }
    }
}
