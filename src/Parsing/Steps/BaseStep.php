<?php

namespace Plasticode\Parsing\Steps;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\ParsingContext;

abstract class BaseStep implements ParsingStepInterface
{
    public function parse(?string $text) : ParsingContext
    {
        $context = ParsingContext::fromText($text);

        return $this->parseContext($context);
    }

    public abstract function parseContext(ParsingContext $context) : ParsingContext;
}
