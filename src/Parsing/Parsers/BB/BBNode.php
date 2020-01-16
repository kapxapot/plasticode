<?php

namespace Plasticode\Parsing\Parsers\BB;

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
     */
    public function __construct(string $tag, array $attributes)
    {
        $this->tag = $tag;
        $this->attributes = $attributes;
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
