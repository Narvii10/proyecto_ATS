<?php

namespace App\Compiler\Lexical;

use Carbon\Carbon;

class LexicalAnalyzer
{
    private const SKILLS = [
        'PHP', 'Python', 'JavaScript', 'TypeScript', 'Java', 'C++', 'C#', 'Ruby', 'Go', 'Rust',
        'HTML', 'CSS', 'React', 'Angular', 'Vue', 'Laravel', 'Django', 'Spring', 'Node.js',
        'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP',
        'Git', 'Linux', 'Agile', 'Scrum', 'REST', 'GraphQL', 'Microservices', 'CI/CD',
        'Machine Learning', 'Deep Learning', 'TensorFlow', 'PyTorch', 'Data Science',
        'Bootstrap', 'Tailwind', 'jQuery', 'Express', 'Flask', 'FastAPI', 'Spring Boot',
        'Oracle', 'SQL Server', 'Firebase', 'Elasticsearch', 'RabbitMQ', 'Kafka',
        'Jenkins', 'Terraform', 'Ansible', 'Nginx', 'Apache', 'Symfony', 'CodeIgniter',
        'Next.js', 'Nuxt.js', 'Svelte', 'Flutter', 'React Native', 'Ionic',
    ];

    private const LANGUAGES = [
        'PHP', 'Python', 'JavaScript', 'TypeScript', 'Java', 'C', 'C++', 'C#', 'Ruby', 'Go',
        'Rust', 'Swift', 'Kotlin', 'Scala', 'R', 'MATLAB', 'Perl', 'Lua', 'Haskell', 'Erlang',
        'Dart', 'Elixir', 'Clojure', 'F#', 'Visual Basic', 'COBOL', 'Fortran',
        'Bash', 'PowerShell', 'SQL', 'HTML', 'CSS', 'SASS',
    ];

    /** Mapea variantes comunes (en minúsculas) a su nombre canónico de habilidad/lenguaje. */
    private const SKILL_ALIASES = [
        'nodejs'       => 'Node.js',
        'node js'      => 'Node.js',
        'reactjs'      => 'React',
        'react.js'     => 'React',
        'vuejs'        => 'Vue',
        'vue.js'       => 'Vue',
        'nextjs'       => 'Next.js',
        'next.js'      => 'Next.js',
        'nuxtjs'       => 'Nuxt.js',
        'angularjs'    => 'Angular',
        'springboot'   => 'Spring Boot',
        'spring-boot'  => 'Spring Boot',
        'tailwindcss'  => 'Tailwind',
        'k8s'          => 'Kubernetes',
        'golang'       => 'Go',
        'mongo'        => 'MongoDB',
        'mongodb'      => 'MongoDB',
        'postgres'     => 'PostgreSQL',
        'python3'      => 'Python',
        'typescript'   => 'TypeScript',
        'elasticsearch'=> 'Elasticsearch',
        'rest api'     => 'REST',
        'restful'      => 'REST',
        'github'       => 'Git',
        'gitlab'       => 'Git',
        'ci/cd'        => 'CI/CD',
        'devops'       => 'CI/CD',
    ];

    /**
     * Punto de entrada del análisis léxico.
     * Recorre el texto del CV línea por línea y extrae todos los tokens reconocidos.
     * @return array{symbolTable: SymbolTable, errors: LexicalError[]}
     */
    public function analyze(string $text): array
    {
        $symbolTable = new SymbolTable();
        $errors      = [];
        $lines       = explode("\n", $text);

        foreach ($lines as $lineIndex => $line) {
            $lineNum = $lineIndex + 1;

            // "Nombre: X" tiene máxima prioridad — se ejecuta antes que la detección por regex genérica
            $this->findLabeledName($line, $lineNum, $symbolTable);

            $this->findEmails($line, $lineNum, $symbolTable, $errors);
            $this->findPhones($line, $lineNum, $symbolTable, $errors);
            $this->findDates($line, $lineNum, $symbolTable, $errors);

            // Solo buscamos nombres en las primeras 15 líneas para reducir falsos positivos
            if ($lineNum <= 15) {
                $this->findNames($line, $lineNum, $symbolTable, $errors);
            }

            // Tokens que dependen del contexto (universidad, carrera, años de experiencia, etc.)
            $this->findContextualTokens($line, $lineNum, $symbolTable);
            // Tokens de diccionario: habilidades y lenguajes de programación
            $this->findDictionaryTokens($line, $lineNum, $symbolTable);
        }

        return ['symbolTable' => $symbolTable, 'errors' => $errors];
    }

    // ---------------------------------------------------------------
    // LABELED NAME ("Nombre: Sofía Hernández") — highest priority
    // ---------------------------------------------------------------
    private function findLabeledName(string $line, int $lineNum, SymbolTable $st): void
    {
        if ($st->firstOfType(Token::TOKEN_NAME)) return;

        if (preg_match('/^(?:nombre(?: completo)?|name|candidato|full name)\s*[:\-]\s*(.+)/i', trim($line), $m)) {
            $val = trim($m[1]);
            if (mb_strlen($val, 'UTF-8') > 2) {
                $pos = mb_strpos($line, $val);
                $st->add(new Token(Token::TOKEN_NAME, $val, $lineNum, (int) $pos));
            }
        }
    }

    // ---------------------------------------------------------------
    // EMAIL
    // ---------------------------------------------------------------
    private function findEmails(string $line, int $lineNum, SymbolTable $st, array &$errors): void
    {
        $valid   = '/\b[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}\b/';
        $invalid = '/\b[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]*(?:\.[a-zA-Z]{0,1})?\b/';

        if (preg_match_all($valid, $line, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as [$val, $pos]) {
                $st->add(new Token(Token::TOKEN_EMAIL, $val, $lineNum, $pos));
            }
        }

        if (preg_match_all($invalid, $line, $all) && !preg_match($valid, $line)) {
            foreach ($all[0] as $val) {
                if (str_contains($val, '@')) {
                    $errors[] = new LexicalError(
                        'INVALID_EMAIL',
                        $val,
                        $lineNum,
                        "Email inválido: '$val' — formato incorrecto o dominio faltante."
                    );
                }
            }
        }
    }

    // ---------------------------------------------------------------
    // PHONE
    // ---------------------------------------------------------------
    private function findPhones(string $line, int $lineNum, SymbolTable $st, array &$errors): void
    {
        $phonePattern  = '/(?<!\w)(\+?[\d][\d\s\-\.\(\)]{6,17}\d)(?!\w)/';
        $letterPattern = '/(?:tel[éeE]?fono|phone|cel|móvil|movil)[^\n]*?([A-Za-z]{2,}[\d]+|[\d]+[A-Za-z]{2,})/i';

        if (preg_match_all($phonePattern, $line, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as [$val, $pos]) {
                $clean = preg_replace('/\s+/', ' ', trim($val));

                // Ignorar rangos de año tipo "2023-2024" — no son teléfonos
                if (preg_match('/^(19|20)\d{2}\s*[\-\/]\s*(19|20)\d{2}$/', $clean)) {
                    continue;
                }
                // Ignorar años sueltos de 4 dígitos
                if (preg_match('/^(19|20)\d{2}$/', preg_replace('/\D/', '', $clean))) {
                    continue;
                }

                if (strlen(preg_replace('/\D/', '', $clean)) >= 7) {
                    $st->add(new Token(Token::TOKEN_PHONE, $clean, $lineNum, $pos));
                }
            }
        }

        if (preg_match($letterPattern, $line, $m)) {
            $errors[] = new LexicalError(
                'PHONE_WITH_LETTERS',
                $m[1] ?? $m[0],
                $lineNum,
                "Número de teléfono con letras detectado: '{$m[0]}'"
            );
        }
    }

    // ---------------------------------------------------------------
    // DATE
    // ---------------------------------------------------------------
    private function findDates(string $line, int $lineNum, SymbolTable $st, array &$errors): void
    {
        $patterns = [
            '/\b(\d{2})\/(\d{2})\/(\d{4})\b/',          // DD/MM/YYYY
            '/\b(\d{2})-(\d{2})-(\d{4})\b/',            // DD-MM-YYYY
            '/\b(0?[1-9]|1[0-2])-(\d{4})\b/',           // MM-YYYY
            '/\b(0?[1-9]|1[0-2])\/(\d{4})\b/',          // MM/YYYY
            '/\b(19|20)\d{2}\b/',                        // YYYY
        ];

        $today = now();

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $line, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as [$val, $pos]) {
                    $st->add(new Token(Token::TOKEN_DATE, $val, $lineNum, $pos));

                    // Check for future graduation dates
                    if (str_contains(mb_strtolower($line), 'graduaci') ||
                        str_contains(mb_strtolower($line), 'egres') ||
                        str_contains(mb_strtolower($line), 'titulaci')) {
                        try {
                            $parsed = Carbon::parse(str_replace('/', '-', $val));
                            if ($parsed->isFuture()) {
                                $errors[] = new LexicalError(
                                    'DATE_FUTURE_GRADUATION',
                                    $val,
                                    $lineNum,
                                    "Fecha de graduación futura: '$val' está en el futuro."
                                );
                            }
                        } catch (\Exception) {
                        }
                    }
                }
            }
        }
    }

    // ---------------------------------------------------------------
    // NAME
    // ---------------------------------------------------------------
    private function findNames(string $line, int $lineNum, SymbolTable $st, array &$errors): void
    {
        // Frases que parecen nombres pero son títulos del documento — se omiten
        static $titleBlacklist = [
            'Currículum Vitae', 'Curriculum Vitae', 'Hoja De Vida', 'Hoja de Vida',
            'Resume', 'Datos Personales', 'Información Personal',
        ];
        static $capsBlacklist = [
            'CURRÍCULUM VITAE', 'CURRICULUM VITAE', 'HOJA DE VIDA',
            'DATOS PERSONALES', 'INFORMACIÓN PERSONAL', 'RESUME',
        ];

        // Nombre en Title Case: "Sofía Hernández"
        $validName = '/\b([A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]{1,}(?:\s+[A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]{1,}){1,4})\b/u';

        if (preg_match_all($validName, $line, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as [$val, $pos]) {
                $wordCount = str_word_count($val);
                if ($wordCount >= 2 && $wordCount <= 5 && !in_array($val, $titleBlacklist)) {
                    $st->add(new Token(Token::TOKEN_NAME, $val, $lineNum, $pos));
                }
            }
        }

        // Nombres en MAYÚSCULAS en las primeras 5 líneas: "SOFÍA HERNÁNDEZ" (frecuente en CVs formales)
        if ($lineNum <= 5 && !$st->firstOfType(Token::TOKEN_NAME)) {
            $trimmed = trim($line);
            if (preg_match('/^([A-ZÁÉÍÓÚÜÑ]{2,}(?:\s+[A-ZÁÉÍÓÚÜÑ]{2,}){1,3})$/u', $trimmed, $m)) {
                $upper = mb_strtoupper($m[1], 'UTF-8');
                if (!in_array($upper, $capsBlacklist)) {
                    $words = preg_split('/\s+/', $m[1]);
                    $words = array_filter($words, fn($w) => mb_strlen($w, 'UTF-8') >= 2);
                    if (count($words) >= 2 && count($words) <= 4) {
                        $title = mb_convert_case($m[1], MB_CASE_TITLE, 'UTF-8');
                        $st->add(new Token(Token::TOKEN_NAME, $title, $lineNum, 0));
                    }
                }
            }
        }

        // Nombre inválido: contiene dígitos o símbolos donde se esperaba texto de nombre
        $invalidName = '/\b([A-Z][a-z]+\s+(?:[A-Z][a-z]+\s+)*[A-Z][a-z]+)\b/';
        if (preg_match_all('/\b([A-Za-z]+[\d!@#$%^&*]+[A-Za-z]*(?:\s+\S+){0,3})\b/', $line, $inv)) {
            foreach ($inv[1] as $val) {
                if (preg_match('/\d/', $val) && preg_match('/[A-Z]/', $val)) {
                    $errors[] = new LexicalError(
                        'INVALID_NAME_CHARS',
                        $val,
                        $lineNum,
                        "Nombre con caracteres inválidos: '$val'"
                    );
                }
            }
        }
    }

    // ---------------------------------------------------------------
    // CONTEXTUAL TOKENS (keyword + value on same/adjacent line)
    // ---------------------------------------------------------------
    private function findContextualTokens(string $line, int $lineNum, SymbolTable $st): void
    {
        $lower = mb_strtolower($line);

        // TOKEN_AGE
        if (preg_match('/(?:edad|age|años)\D{0,15}(\b(?:1[6-9]|[2-7]\d|80)\b)/i', $line, $m)) {
            $st->add(new Token(Token::TOKEN_AGE, $m[1], $lineNum, strpos($line, $m[1])));
        }

        // TOKEN_UNIVERSITY
        if (preg_match('/(?:universidad|university|instituto tecnológico|instituto tecnologico|institut)\s*[:\-]?\s*(.+)/i', $line, $m)) {
            $val = trim($m[1]);
            if (strlen($val) > 3) {
                $st->add(new Token(Token::TOKEN_UNIVERSITY, $val, $lineNum, strpos($line, $val)));
            }
        }

        // TOKEN_CAREER
        if (preg_match('/(?:carrera|degree|licenciatura|ingeniería|ingenieria|maestría|maestria|doctorado)\s*[:\-]?\s*(.+)/i', $line, $m)) {
            $val = trim($m[1]);
            if (strlen($val) > 3) {
                $st->add(new Token(Token::TOKEN_CAREER, $val, $lineNum, strpos($line, $val)));
            }
        }

        // TOKEN_EXPERIENCE_YEARS
        if (preg_match(
            '/(\d+)\s*\+?\s*a[ñn]os?\s+de\s+experiencia'   // "5 años de experiencia"
            . '|experiencia\s+(?:de\s+)?(\d+)\s*\+?\s*a[ñn]os?' // "experiencia de 5 años"
            . '|(\d+)\s*\+?\s*a[ñn]os?\s+(?:de\s+)?exp(?:eriencia)?\.?' // "5 años exp."
            . '|(\d+)\s*\+?\s*years?\s+(?:of\s+)?experience' // "5 years of experience"
            . '|(\d+)\s*\+?\s*years?\s+experience'           // "5+ years experience"
            . '/i', $line, $m
        )) {
            $years = $m[1] ?: ($m[2] ?: ($m[3] ?: ($m[4] ?: $m[5])));
            if ($years) {
                $st->add(new Token(Token::TOKEN_EXPERIENCE_YEARS, (string)(int)$years, $lineNum, strpos($line, $years)));
            }
        }

        // TOKEN_CERTIFICATION
        if (preg_match('/(?:certificaci[oó]n|certified|certificate|certificado)\s*[:\-]?\s*(.+)/i', $line, $m)) {
            $val = trim($m[1]);
            if (strlen($val) > 3) {
                $st->add(new Token(Token::TOKEN_CERTIFICATION, $val, $lineNum, strpos($line, $val)));
            }
        }
    }

    // ---------------------------------------------------------------
    // DICTIONARY TOKENS (skills + programming languages)
    // ---------------------------------------------------------------
    private function findDictionaryTokens(string $line, int $lineNum, SymbolTable $st): void
    {
        foreach (self::LANGUAGES as $lang) {
            $escaped = preg_quote($lang, '/');
            if (preg_match('/(?<![A-Za-z])' . $escaped . '(?![A-Za-z])/i', $line)) {
                $st->add(new Token(Token::TOKEN_LANGUAGE, $lang, $lineNum, mb_stripos($line, $lang)));
            }
        }

        $languageSet = array_map('strtolower', self::LANGUAGES);

        foreach (self::SKILLS as $skill) {
            if (in_array(strtolower($skill), $languageSet)) {
                continue; // ya fue capturado como lenguaje, no duplicar
            }
            $escaped = preg_quote($skill, '/');
            if (preg_match('/(?<![A-Za-z])' . $escaped . '(?![A-Za-z])/i', $line)) {
                $st->add(new Token(Token::TOKEN_SKILL, $skill, $lineNum, mb_stripos($line, $skill)));
            }
        }

        // Buscar alias no cubiertos por los diccionarios principales (ej: "k8s" → "Kubernetes")
        $alreadyInDicts = array_merge(
            array_map('strtolower', self::SKILLS),
            array_map('strtolower', self::LANGUAGES)
        );
        foreach (self::SKILL_ALIASES as $alias => $canonical) {
            if (in_array(strtolower($canonical), $alreadyInDicts)) {
                continue; // el canónico ya fue detectado por los loops anteriores
            }
            $escaped = preg_quote($alias, '/');
            if (preg_match('/(?<![A-Za-z0-9])' . $escaped . '(?![A-Za-z0-9])/i', $line)) {
                $type = in_array(strtolower($canonical), array_map('strtolower', self::LANGUAGES))
                    ? Token::TOKEN_LANGUAGE
                    : Token::TOKEN_SKILL;
                $st->add(new Token($type, $canonical, $lineNum, mb_stripos($line, $alias)));
            }
        }
    }
}
