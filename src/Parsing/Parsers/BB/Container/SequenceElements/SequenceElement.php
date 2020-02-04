<?php

namespace Plasticode\Parsing\Parsers\BB\Container\SequenceElements;

use Plasticode\Parsing\Parsers\BB\Container\Nodes\Node;

class SequenceElement
{
    /** @var string */
    public $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function toNode() : Node
    {
        return new Node($this->text);
    }
}
