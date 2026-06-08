<?php

namespace App\Compiler\Semantic;

class SemanticError
{
    public function __construct(
        public readonly string $code,
        public readonly string $field,
        public readonly string $severity,
        public readonly string $message,
        public readonly string $suggestion,
    ) {}

    public function toArray(): array
    {
        return [
            'code'       => $this->code,
            'field'      => $this->field,
            'severity'   => $this->severity,
            'message'    => $this->message,
            'suggestion' => $this->suggestion,
        ];
    }
}
