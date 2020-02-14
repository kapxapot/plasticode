<?php

namespace Plasticode\Parsing\Parsers\BB\Nodes;

use Plasticode\Util\Arrays;

class TagNode extends Node
{
    /** @var string */
    private $tag;

    /** @var string[] */
    private $attributes;

    /** @var Node[] */
    private $children = [];

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
     * Tag.
     *
     * @return string
     */
    public function tag() : string
    {
        return $this->tag;
    }

    /**
     * Attributes.
     *
     * @return string[]
     */
    public function attributes() : array
    {
        return $this->attributes;
    }

    /**
     * Child nodes.
     *
     * @return Node[]
     */
    public function children() : array
    {
        return $this->children;
    }

    /**
     * Returns first attribute or null.
     *
     * @return string|null
     */
    public function firstAttribute() : ?string
    {
        return Arrays::first($this->attributes);
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
