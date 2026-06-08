<?php

namespace App\Services\Parsers;

class TxtParser
{
    public function parse(string $content): array
    {
        return [
            'format'  => 'txt',
            'text'    => $content,
            'lines'   => explode("\n", $content),
            'sections'=> $this->detectSections($content),
        ];
    }

    private function detectSections(string $content): array
    {
        $sections = [];
        $lines    = explode("\n", $content);

        foreach ($lines as $i => $line) {
            $trimmed = mb_strtolower(trim($line));
            $sectionType = $this->matchSectionKeyword($trimmed);
            if ($sectionType) {
                $sections[$sectionType] = $i + 1;
            }
        }

        return $sections;
    }

    private function matchSectionKeyword(string $line): ?string
    {
        $map = [
            'PersonalData' => ['datos personales', 'personal info', 'perfil', 'información personal'],
            'Contact'      => ['contacto', 'contact', 'información de contacto'],
            'Education'    => ['educación', 'education', 'formación académica', 'estudios', 'formacion academica'],
            'Experience'   => ['experiencia', 'experience', 'experiencia laboral', 'experiencia profesional'],
            'Skills'       => ['habilidades', 'skills', 'competencias', 'tecnologías', 'tecnologias'],
        ];

        foreach ($map as $section => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($line, $keyword)) {
                    return $section;
                }
            }
        }

        return null;
    }
}
