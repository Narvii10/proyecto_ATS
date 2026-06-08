<?php

namespace App\Compiler\Semantic;

use App\Compiler\AST\ASTNode;
use App\Compiler\Lexical\SymbolTable;
use App\Compiler\Lexical\Token;
use Carbon\Carbon;

class SemanticAnalyzer
{
    /** @return SemanticError[] */
    public function analyze(ASTNode $ast, SymbolTable $symbolTable): array
    {
        $errors = [];

        $this->checkAge($symbolTable, $errors);
        $this->checkExperience($symbolTable, $errors);
        $this->checkGraduationDates($symbolTable, $errors);
        $this->checkJobDateRanges($ast, $errors);
        $this->checkPhones($symbolTable, $errors);
        $this->checkDuplicateCertifications($symbolTable, $errors);
        $this->checkExperienceGaps($ast, $errors);
        $this->checkExperienceMismatch($ast, $symbolTable, $errors);
        $this->checkSkillConflict($ast, $symbolTable, $errors);
        $this->checkAgeExperienceConflict($symbolTable, $errors);

        return $errors;
    }

    private function checkAge(SymbolTable $st, array &$errors): void
    {
        $ageToken = $st->firstOfType(Token::TOKEN_AGE);
        if (!$ageToken) {
            return;
        }
        $age = (int) $ageToken->value;
        if ($age < 16 || $age > 80) {
            $errors[] = new SemanticError(
                'AGE_INVALID',
                'age',
                'error',
                "La edad $age es inválida (debe estar entre 16 y 80 años).",
                'Verifique que la edad indicada en el CV sea correcta.'
            );
        }
    }

    private function checkExperience(SymbolTable $st, array &$errors): void
    {
        $expToken = $st->firstOfType(Token::TOKEN_EXPERIENCE_YEARS);
        if (!$expToken) {
            return;
        }
        $years = (int) $expToken->value;
        if ($years < 0) {
            $errors[] = new SemanticError(
                'EXP_NEGATIVE',
                'experience_years',
                'error',
                "Los años de experiencia no pueden ser negativos ($years).",
                'Corrija el valor de años de experiencia en el CV.'
            );
        } elseif ($years > 50) {
            $errors[] = new SemanticError(
                'EXP_IMPOSSIBLE',
                'experience_years',
                'error',
                "Los años de experiencia ($years) son impossibles para una carrera laboral.",
                'Verifique el número de años de experiencia declarados.'
            );
        }
    }

    private function checkGraduationDates(SymbolTable $st, array &$errors): void
    {
        foreach ($st->byType(Token::TOKEN_DATE) as $token) {
            try {
                $date = Carbon::parse(str_replace('/', '-', $token->value));
                if ($date->isFuture() && $date->year > now()->year) {
                    $errors[] = new SemanticError(
                        'GRAD_FUTURE',
                        'graduation_date',
                        'error',
                        "Fecha de graduación '{$token->value}' está en el futuro.",
                        'Corrija la fecha de graduación o elimínela si aún no se ha obtenido el título.'
                    );
                }
            } catch (\Exception) {
            }
        }
    }

    private function checkJobDateRanges(ASTNode $ast, array &$errors): void
    {
        foreach ($ast->children as $child) {
            if ($child->type !== 'ExperienceNode') {
                continue;
            }
            foreach ($child->children as $job) {
                if ($job->type !== 'JobNode') {
                    continue;
                }
                foreach ($job->children as $period) {
                    if ($period->type !== 'PeriodNode') {
                        continue;
                    }
                    $start = $period->attributes['start'] ?? null;
                    $end   = $period->attributes['end']   ?? null;
                    if ($start && $end) {
                        try {
                            $s = Carbon::parse(str_replace('/', '-', $start));
                            $e = Carbon::parse(str_replace('/', '-', $end));
                            if ($s->gt($e)) {
                                $errors[] = new SemanticError(
                                    'DATE_RANGE_INVALID',
                                    'job_dates',
                                    'error',
                                    "Fecha de inicio ($start) es posterior a la fecha de fin ($end).",
                                    'Verifique el orden de las fechas de experiencia laboral.'
                                );
                            }
                        } catch (\Exception) {
                        }
                    }
                }
            }
        }
    }

    private function checkPhones(SymbolTable $st, array &$errors): void
    {
        foreach ($st->byType(Token::TOKEN_PHONE) as $token) {
            $digits = preg_replace('/\D/', '', $token->value);
            if (strlen($digits) < 7 || strlen($digits) > 15) {
                $errors[] = new SemanticError(
                    'PHONE_INVALID',
                    'phone',
                    'error',
                    "Número de teléfono '{$token->value}' no cumple con el formato E.164.",
                    'Use el formato internacional: +52 (55) 1234-5678 o similar.'
                );
            }
        }
    }

    private function checkDuplicateCertifications(SymbolTable $st, array &$errors): void
    {
        $certs = array_map(
            fn(Token $t) => mb_strtolower(trim($t->value)),
            $st->byType(Token::TOKEN_CERTIFICATION)
        );
        $duplicates = array_filter(array_count_values($certs), fn(int $c) => $c > 1);

        foreach (array_keys($duplicates) as $cert) {
            $errors[] = new SemanticError(
                'CERT_DUPLICATE',
                'certifications',
                'warning',
                "Certificación duplicada detectada: '$cert'.",
                'Elimine las entradas duplicadas de certificaciones.'
            );
        }
    }

    private function checkExperienceGaps(ASTNode $ast, array &$errors): void
    {
        $periods = [];
        foreach ($ast->children as $child) {
            if ($child->type !== 'ExperienceNode') {
                continue;
            }
            foreach ($child->children as $job) {
                foreach ($job->children as $period) {
                    if ($period->type === 'PeriodNode') {
                        $start = $period->attributes['start'] ?? null;
                        $end   = $period->attributes['end']   ?? null;
                        if ($start && $end) {
                            $periods[] = ['start' => $start, 'end' => $end];
                        }
                    }
                }
            }
        }

        for ($i = 0; $i < count($periods) - 1; $i++) {
            try {
                $end   = Carbon::parse(str_replace('/', '-', $periods[$i]['end']));
                $start = Carbon::parse(str_replace('/', '-', $periods[$i + 1]['start']));
                $gapMonths = (int) $end->diffInMonths($start);
                if ($gapMonths > 12) {
                    $errors[] = new SemanticError(
                        'EXP_GAP',
                        'experience_dates',
                        'warning',
                        "Brecha de $gapMonths meses entre empleos ({$periods[$i]['end']} → {$periods[$i+1]['start']}).",
                        'Considere explicar los períodos de inactividad laboral.'
                    );
                }
            } catch (\Exception) {
            }
        }
    }

    private function checkExperienceMismatch(ASTNode $ast, SymbolTable $st, array &$errors): void
    {
        $expToken = $st->firstOfType(Token::TOKEN_EXPERIENCE_YEARS);
        if (!$expToken) {
            return;
        }
        $statedYears = (int) $expToken->value;

        $totalMonths = 0;
        foreach ($ast->children as $child) {
            if ($child->type !== 'ExperienceNode') {
                continue;
            }
            foreach ($child->children as $job) {
                foreach ($job->children as $period) {
                    $totalMonths += $period->attributes['months_duration'] ?? 0;
                }
            }
        }

        $computedYears = $totalMonths / 12;
        if (abs($statedYears - $computedYears) > 1 && $totalMonths > 0) {
            $errors[] = new SemanticError(
                'EXP_MISMATCH',
                'experience_years',
                'warning',
                sprintf(
                    'Años declarados (%d) no coinciden con la suma de fechas de empleo (%.1f años).',
                    $statedYears,
                    $computedYears
                ),
                'Revise que los años de experiencia declarados coincidan con su historial laboral.'
            );
        }
    }

    private function checkSkillConflict(ASTNode $ast, SymbolTable $st, array &$errors): void
    {
        $expToken = $st->firstOfType(Token::TOKEN_EXPERIENCE_YEARS);
        if (!$expToken || (int) $expToken->value < 10) {
            return;
        }

        $careerToken = $st->firstOfType(Token::TOKEN_CAREER);
        if ($careerToken) {
            $careerLower = mb_strtolower($careerToken->value);
            if (str_contains($careerLower, 'junior') || str_contains($careerLower, 'trainee')) {
                $errors[] = new SemanticError(
                    'SKILL_CONFLICT',
                    'career_level',
                    'warning',
                    'El CV indica nivel Junior/Trainee pero declara ' . $expToken->value . '+ años de experiencia.',
                    'Actualice el nivel de experiencia o la descripción del puesto.'
                );
            }
        }
    }

    private function checkAgeExperienceConflict(SymbolTable $st, array &$errors): void
    {
        $ageToken = $st->firstOfType(Token::TOKEN_AGE);
        $expToken = $st->firstOfType(Token::TOKEN_EXPERIENCE_YEARS);

        if (!$ageToken || !$expToken) {
            return;
        }

        $age   = (int) $ageToken->value;
        $years = (int) $expToken->value;

        if ($years + 18 > $age) {
            $errors[] = new SemanticError(
                'AGE_EXP_CONFLICT',
                'age_experience',
                'warning',
                "Conflicto: con $age años y $years años de experiencia, habría empezado a trabajar antes de los 18.",
                'Verifique la edad y los años de experiencia declarados.'
            );
        }
    }
}
