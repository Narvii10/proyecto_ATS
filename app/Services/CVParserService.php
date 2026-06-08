<?php

namespace App\Services;

use App\Services\Parsers\JsonParser;
use App\Services\Parsers\PdfParser;
use App\Services\Parsers\TxtParser;
use App\Services\Parsers\XmlParser;
use Illuminate\Http\UploadedFile;

class CVParserService
{
    public function parse(UploadedFile $file): array
    {
        $format = strtolower($file->getClientOriginalExtension());

        return match ($format) {
            'txt'   => (new TxtParser())->parse($file->get()),
            'json'  => (new JsonParser())->parse($file->get()),
            'xml'   => (new XmlParser())->parse($file->get()),
            'pdf'   => (new PdfParser())->parse($file->getRealPath()),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };
    }

    public function parseFromPath(string $path, string $format): array
    {
        return match ($format) {
            'txt'   => (new TxtParser())->parse(file_get_contents($path)),
            'json'  => (new JsonParser())->parse(file_get_contents($path)),
            'xml'   => (new XmlParser())->parse(file_get_contents($path)),
            'pdf'   => (new PdfParser())->parse($path),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };
    }
}
