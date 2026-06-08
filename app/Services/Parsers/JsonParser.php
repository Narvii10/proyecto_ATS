<?php

namespace App\Services\Parsers;

class JsonParser
{
    public function parse(string $content): array
    {
        $data = json_decode($content, true);

        if ($data === null) {
            return [
                'format' => 'json',
                'text'   => $content,
                'error'  => 'Invalid JSON: ' . json_last_error_msg(),
                'lines'  => explode("\n", $content),
            ];
        }

        return [
            'format'   => 'json',
            'text'     => $this->flattenToText($data),
            'raw_data' => $data,
            'lines'    => explode("\n", $this->flattenToText($data)),
        ];
    }

    private function flattenToText(array $data, int $depth = 0): string
    {
        $lines = [];
        foreach ($data as $key => $value) {
            $indent = str_repeat('  ', $depth);
            if (is_array($value)) {
                $lines[] = $indent . ucfirst((string) $key) . ':';
                $lines[] = $this->flattenToText($value, $depth + 1);
            } else {
                $lines[] = $indent . ucfirst((string) $key) . ': ' . (string) $value;
            }
        }
        return implode("\n", $lines);
    }
}
