<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\CvDocument;

/**
 * Clasificador local basado en tokens léxicos.
 * No requiere internet — usa los datos ya extraídos por el analizador léxico.
 * Intenta Groq si hay clave configurada; cae al clasificador local si falla.
 */
class AIClassifierService
{
    // ── Mapa de categorías por habilidades / lenguajes ──────────────────────
    private const CATEGORY_RULES = [
        'DevOps / Cloud'              => ['docker','kubernetes','terraform','ansible','jenkins','ci/cd','aws','azure','gcp','linux','nginx','devops','helm','prometheus','grafana'],
        'Desarrollo Backend'          => ['laravel','django','spring','node.js','express','flask','fastapi','ruby on rails','php','python','java','go','rust','postgresql','mysql','mongodb','redis','api rest','microservices'],
        'Desarrollo Frontend'         => ['react','vue','angular','javascript','typescript','html','css','tailwind','next.js','nuxt','webpack','vite','sass','jquery'],
        'Desarrollo Móvil'            => ['android','ios','flutter','react native','swift','kotlin','xcode','firebase'],
        'Ciencia de Datos / IA'       => ['python','machine learning','deep learning','tensorflow','pytorch','pandas','numpy','scikit-learn','data science','nlp','sql','power bi','tableau','r'],
        'Seguridad Informática'       => ['cybersecurity','pentesting','kali linux','burpsuite','owasp','cve','soc','siem','nmap','metasploit','seguridad'],
        'Administración de Sistemas'  => ['linux','windows server','active directory','vmware','bash','powershell','networking','cisco','redes','soporte técnico'],
        'Gestión de Proyectos / QA'   => ['scrum','agile','kanban','jira','trello','testing','qa','selenium','postman','gestión de proyectos','pmp'],
    ];

    // ── Niveles de assessment por score de tokens ────────────────────────────
    private const ASSESSMENT_LEVELS = [
        ['min' => 10, 'label' => 'Candidato fuerte'],
        ['min' => 6,  'label' => 'Buen candidato'],
        ['min' => 3,  'label' => 'Necesita experiencia'],
        ['min' => 0,  'label' => 'No adecuado'],
    ];

    public function classify(Candidate $candidate, string $cvText): bool
    {
        // 1) Intentar con Groq si hay clave
        if (!empty(config('services.groq.key'))) {
            $ok = $this->classifyWithGroq($candidate, $cvText);
            if ($ok) return true;
        }

        // 2) Fallback: clasificador local basado en tokens
        return $this->classifyLocally($candidate, $cvText);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Clasificador LOCAL
    // ────────────────────────────────────────────────────────────────────────
    private function classifyLocally(Candidate $candidate, string $cvText): bool
    {
        $cv = $candidate->cvDocuments()->latest()->with('lexicalTokens')->first();

        $tokens = $cv?->lexicalTokens ?? collect();

        $skills = $tokens->where('type', 'TOKEN_SKILL')->pluck('value')
            ->merge($tokens->where('type', 'TOKEN_LANGUAGE')->pluck('value'))
            ->map(fn($v) => strtolower(trim($v)))
            ->unique()->values();

        $certifications = $tokens->where('type', 'TOKEN_CERTIFICATION')->pluck('value');
        $expYears       = (int) ($tokens->firstWhere('type', 'TOKEN_EXPERIENCE_YEARS')?->value ?? 0);
        $university     = $tokens->firstWhere('type', 'TOKEN_UNIVERSITY')?->value;
        $career         = $tokens->firstWhere('type', 'TOKEN_CAREER')?->value;

        // ── Determinar categoría ─────────────────────────────────────────
        $categoryScores = [];
        foreach (self::CATEGORY_RULES as $cat => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                foreach ($skills as $skill) {
                    if (str_contains($skill, $kw) || str_contains($kw, $skill)) {
                        $score++;
                    }
                }
            }
            $categoryScores[$cat] = $score;
        }
        arsort($categoryScores);
        $topCategory = array_key_first($categoryScores);
        if (($categoryScores[$topCategory] ?? 0) === 0) {
            $topCategory = 'Tecnología de la Información';
        }

        // ── Assessment basado en score compuesto ─────────────────────────
        $tokenScore = min(15, $skills->count())
                    + min(3, $certifications->count())
                    + min(3, $expYears)
                    + min(2, (int) ($university !== null))
                    + min(2, (int) ($career !== null));

        $assessment = 'No adecuado';
        foreach (self::ASSESSMENT_LEVELS as $level) {
            if ($tokenScore >= $level['min']) {
                $assessment = $level['label'];
                break;
            }
        }

        // ── Fortalezas ───────────────────────────────────────────────────
        $strengths = [];
        if ($skills->count() >= 8)   $strengths[] = 'Amplio stack tecnológico (' . $skills->count() . ' tecnologías)';
        if ($skills->count() >= 4)   $strengths[] = 'Conocimiento en ' . $skills->take(3)->implode(', ');
        if ($certifications->count()) $strengths[] = $certifications->count() . ' certificación(es) verificada(s)';
        if ($expYears >= 3)           $strengths[] = "{$expYears} años de experiencia laboral";
        if ($university)              $strengths[] = "Formación universitaria: {$university}";
        if (empty($strengths))        $strengths[] = 'CV registrado correctamente en el sistema';

        // ── Áreas de mejora ──────────────────────────────────────────────
        $weaknesses = [];
        if ($expYears < 2)               $weaknesses[] = 'Poca experiencia laboral documentada';
        if ($certifications->isEmpty())  $weaknesses[] = 'Sin certificaciones registradas';
        if ($skills->count() < 4)        $weaknesses[] = 'Pocas habilidades técnicas detectadas';
        if (!$university)                $weaknesses[] = 'Formación académica no especificada';
        if (empty($weaknesses))          $weaknesses[] = 'Continuar actualizando certificaciones';

        // ── Resumen ──────────────────────────────────────────────────────
        $skillList = $skills->take(4)->implode(', ');
        $expStr    = $expYears > 0 ? "con {$expYears} años de experiencia" : 'en búsqueda de su primera oportunidad';
        $summary   = "Candidato en el área de {$topCategory} {$expStr}.";
        if ($skillList) {
            $summary .= " Domina tecnologías como {$skillList}.";
        }
        if ($certifications->isNotEmpty()) {
            $summary .= " Cuenta con {$certifications->count()} certificación(es) relevante(s).";
        }

        $candidate->update([
            'ai_summary'    => $summary,
            'ai_category'   => $topCategory,
            'ai_assessment' => $assessment,
            'ai_strengths'  => array_values(array_slice($strengths, 0, 4)),
            'ai_weaknesses' => array_values(array_slice($weaknesses, 0, 3)),
        ]);

        return true;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Clasificador GROQ (intento externo)
    // ────────────────────────────────────────────────────────────────────────
    private function classifyWithGroq(Candidate $candidate, string $cvText): bool
    {
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 15]);

            $prompt = <<<PROMPT
Analiza este CV de un candidato en tecnología. Responde ÚNICAMENTE con JSON válido (sin markdown, sin texto extra):
{"name":"Nombre completo del candidato tal como aparece en el CV","summary":"Resumen 2-3 oraciones en español sobre el perfil del candidato","category":"Una de: DevOps / Cloud | Desarrollo Backend | Desarrollo Frontend | Desarrollo Móvil | Ciencia de Datos / IA | Seguridad Informática | Administración de Sistemas | Gestión de Proyectos / QA | Tecnología de la Información","assessment":"Candidato fuerte | Buen candidato | Necesita experiencia | No adecuado","strengths":["fortaleza concreta basada en el CV"],"weaknesses":["área de mejora concreta"]}

Criterios para assessment:
- "Candidato fuerte": perfil sólido con experiencia relevante, habilidades demostradas y formación adecuada
- "Buen candidato": tiene las bases necesarias con algo de experiencia o habilidades relevantes
- "Necesita experiencia": perfil junior o con pocas habilidades documentadas
- "No adecuado": CV con muy poca información técnica relevante

CV:
{$cvText}
PROMPT;

            $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.groq.key'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => 'llama-3.3-70b-versatile',
                    'max_tokens'  => 400,
                    'temperature' => 0.3,
                    'messages'    => [
                        ['role' => 'system', 'content' => 'Responde solo con JSON válido, sin markdown.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $text = $body['choices'][0]['message']['content'] ?? '';
            $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
            $text = preg_replace('/\s*```$/m', '', $text);
            $data = json_decode(trim($text), true);

            if (!$data) return false;

            $updateData = [
                'ai_summary'    => $data['summary']    ?? null,
                'ai_category'   => $data['category']   ?? null,
                'ai_assessment' => $data['assessment'] ?? null,
                'ai_strengths'  => $data['strengths']  ?? [],
                'ai_weaknesses' => $data['weaknesses'] ?? [],
            ];

            // Si el analizador léxico no detectó el nombre, usamos el nombre extraído por Groq
            if (empty($candidate->name) && !empty($data['name'])) {
                $updateData['name'] = trim($data['name']);
            }

            $candidate->update($updateData);

            return true;

        } catch (\Exception $e) {
            \Log::warning('Groq no disponible, usando clasificador local: ' . $e->getMessage());
            return false;
        }
    }
}
