<?php

namespace Plasticode\Parsing\Parsers\BB\Container\SequenceElements;

class SequenceElement
{
    /** @var string */
    public $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }
}
