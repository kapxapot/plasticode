<?php

namespace Plasticode\Parsing\Parsers\BB\Container\Nodes;

class TagNode extends Node
{
    /** @var string */
    public $tag;

    /** @var string[] */
    public $attributes;

    /** @var Node[] */
    public $children = [];

    /**
     * Creates TagNode.
     *
     * @param string $tag
     * @param string[] $attributes
     * @param string $text
     */
    public function __construct(string $tag, array $attributes, string $text)
    {
        parent::__construct($text);

        $this->tag = $tag;
        $this->attributes = $attributes ?? [];
    }

    /**
     * Adds child node.
     *
     * @param Node $node
     * @return void
     */
    public function addChild(Node $node) : void
    {
        $this->children[] = $node;
    }

    /**
     * Reduces tag node to a text node.
     *
     * @return Node
     */
    public function reduce() : Node
    {
        return new Node($this->text);
    }
}
