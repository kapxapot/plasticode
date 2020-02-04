<?php

namespace Plasticode\Parsing\Parsers\BB\Container;

class BBNode
{
    /** @var string */
    public $tag;

    /** @var string[] */
    public $attributes = [];

    /** @var string */
    public $text;

    /** @var array */
    public $content = [];

    /**
     * Creates BBNode.
     *
     * @param string $tag
     * @param string[] $attributes
     * @param string $text
     */
    public function __construct(string $tag, array $attributes, string $text)
    {
        $this->tag = $tag;
        $this->attributes = $attributes;
        $this->text = $text;
    }

    /**
     * Adds content item.
     *
     * @param mixed $item
     * @return void
     */
    public function addContent($item) : void
    {
        $this->content[] = $item;
    }
}
