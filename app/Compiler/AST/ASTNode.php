<?php

namespace App\Compiler\AST;

class ASTNode
{
    /** @var ASTNode[] */
    public array $children = [];

    public function __construct(
        public string $type,
        public mixed  $value      = null,
        public array  $attributes = [],
    ) {}

    public function addChild(ASTNode $node): void
    {
        $this->children[] = $node;
    }

    public function toArray(): array
    {
        return [
            'type'       => $this->type,
            'value'      => $this->value,
            'attributes' => $this->attributes,
            'children'   => array_map(fn(ASTNode $c) => $c->toArray(), $this->children),
        ];
    }
}
