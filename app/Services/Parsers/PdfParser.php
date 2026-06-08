<?php

namespace App\Services\Parsers;

use Smalot\PdfParser\Parser;

class PdfParser
{
    public function parse(string $filePath): array
    {
        $parser   = new Parser();
        $pdf      = $parser->parseFile($filePath);
        $text     = $pdf->getText();
        $pages    = $pdf->getPages();
        $pageCount = count($pages);

        return [
            'format'    => 'pdf',
            'text'      => $text,
            'page_count'=> $pageCount,
            'lines'     => explode("\n", $text),
        ];
    }
}
