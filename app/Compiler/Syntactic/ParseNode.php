<?php

namespace App\Compiler\Syntactic;

class ParseNode
{
    /** @var ParseNode[] */
    public array $children = [];

    public function __construct(
        public string  $type,
        public mixed   $value = null,
        public int     $line  = 0,
    ) {}

    public function addChild(ParseNode $node): void
    {
        $this->children[] = $node;
    }

    public function toArray(): array
    {
        return [
            'type'     => $this->type,
            'value'    => $this->value,
            'line'     => $this->line,
            'children' => array_map(fn(ParseNode $c) => $c->toArray(), $this->children),
        ];
    }
}
