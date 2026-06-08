<?php

namespace App\Compiler\Lexical;

class LexicalError
{
    public function __construct(
        public readonly string  $code,
        public readonly ?string $value,
        public readonly ?int    $line,
        public readonly string  $message,
    ) {}

    public function toArray(): array
    {
        return [
            'code'    => $this->code,
            'value'   => $this->value,
            'line'    => $this->line,
            'message' => $this->message,
        ];
    }
}
