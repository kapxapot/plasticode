<?php

namespace Plasticode\Hydrators\Basic;

use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\ParsingContext;

abstract class ParsingHydrator extends Hydrator
{
    private ParserInterface $parser;

    public function __construct(
        ParserInterface $parser
    )
    {
        $this->parser = $parser;
    }

    protected function parse(?string $text) : ParsingContext
    {
        if (strlen($text) == 0) {
            return null;
        }

        $context = $this->parser->parse($text);
        $context = $this->parser->renderLinks($context);

        return $context;
    }
}
