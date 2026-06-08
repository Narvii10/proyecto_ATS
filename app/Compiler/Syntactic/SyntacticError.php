<?php

namespace App\Compiler\Syntactic;

class SyntacticError
{
    public function __construct(
        public readonly string  $code,
        public readonly ?string $section,
        public readonly ?int    $line,
        public readonly string  $message,
    ) {}

    public function toArray(): array
    {
        return [
            'code'    => $this->code,
            'section' => $this->section,
            'line'    => $this->line,
            'message' => $this->message,
        ];
    }
}
