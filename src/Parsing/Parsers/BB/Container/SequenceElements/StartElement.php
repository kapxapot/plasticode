<?php

namespace Plasticode\Parsing\Parsers\BB\Container\SequenceElements;

use Plasticode\Parsing\Parsers\BB\Container\Nodes\TagNode;

class StartElement extends EndElement
{
    /** @var string[] */
    public $attributes;

    public function __construct(string $tag, array $attributes, string $text)
    {
        parent::__construct($tag, $text);

        $this->attributes = $attributes;
    }

    public function toTagNode() : TagNode
    {
        return new TagNode($this->tag, $this->attributes, $this->text);
    }
}
