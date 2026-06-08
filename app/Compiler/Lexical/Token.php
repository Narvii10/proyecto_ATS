<?php

namespace App\Compiler\Lexical;

class Token
{
    public const TOKEN_NAME             = 'TOKEN_NAME';
    public const TOKEN_EMAIL            = 'TOKEN_EMAIL';
    public const TOKEN_PHONE            = 'TOKEN_PHONE';
    public const TOKEN_AGE              = 'TOKEN_AGE';
    public const TOKEN_UNIVERSITY       = 'TOKEN_UNIVERSITY';
    public const TOKEN_CAREER           = 'TOKEN_CAREER';
    public const TOKEN_SKILL            = 'TOKEN_SKILL';
    public const TOKEN_LANGUAGE         = 'TOKEN_LANGUAGE';
    public const TOKEN_EXPERIENCE_YEARS = 'TOKEN_EXPERIENCE_YEARS';
    public const TOKEN_CERTIFICATION    = 'TOKEN_CERTIFICATION';
    public const TOKEN_DATE             = 'TOKEN_DATE';

    public function __construct(
        public readonly string $type,
        public readonly string $value,
        public readonly int    $line,
        public readonly int    $position,
    ) {}

    public function toArray(): array
    {
        return [
            'type'     => $this->type,
            'value'    => $this->value,
            'line'     => $this->line,
            'position' => $this->position,
        ];
    }
}
