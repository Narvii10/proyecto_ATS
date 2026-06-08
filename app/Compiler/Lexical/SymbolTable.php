<?php

namespace App\Compiler\Lexical;

class SymbolTable
{
    /** @var Token[] */
    private array $tokens = [];

    public function add(Token $token): void
    {
        $this->tokens[] = $token;
    }

    /** @return Token[] */
    public function all(): array
    {
        return $this->tokens;
    }

    /** @return Token[] */
    public function byType(string $type): array
    {
        return array_values(array_filter($this->tokens, fn(Token $t) => $t->type === $type));
    }

    public function firstOfType(string $type): ?Token
    {
        foreach ($this->tokens as $token) {
            if ($token->type === $type) {
                return $token;
            }
        }
        return null;
    }

    public function count(): int
    {
        return count($this->tokens);
    }

    public function toArray(): array
    {
        return array_map(fn(Token $t) => $t->toArray(), $this->tokens);
    }
}
