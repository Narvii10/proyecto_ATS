<?php

namespace App\Services\Parsers;

class XmlParser
{
    public function parse(string $content): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);

        if ($xml === false) {
            return [
                'format' => 'xml',
                'text'   => strip_tags($content),
                'error'  => 'Invalid XML',
                'lines'  => explode("\n", strip_tags($content)),
            ];
        }

        $text = $this->xmlToText($xml);

        return [
            'format'   => 'xml',
            'text'     => $text,
            'raw_data' => json_decode(json_encode($xml), true),
            'lines'    => explode("\n", $text),
        ];
    }

    private function xmlToText(\SimpleXMLElement $xml, int $depth = 0): string
    {
        $lines  = [];
        $indent = str_repeat('  ', $depth);

        foreach ($xml->children() as $name => $child) {
            $value = trim((string) $child);
            if ($child->count() > 0) {
                $lines[] = $indent . ucfirst($name) . ':';
                $lines[] = $this->xmlToText($child, $depth + 1);
            } elseif ($value !== '') {
                $lines[] = $indent . ucfirst($name) . ': ' . $value;
            }
        }

        return implode("\n", $lines);
    }
}
