<?php

namespace Plasticode\Parsing\Parsers\BB\Container\SequenceElements;

class EndElement extends SequenceElement
{
    /** @var string */
    public $tag;

    public function __construct(string $tag, string $text)
    {
        parent::__construct($text);

        $this->tag = $tag;
    }
}
