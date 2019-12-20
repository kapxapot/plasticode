<?php

namespace Plasticode\Parsing\Steps;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Util\Text;

class BrsToPsStep implements ParsingStepInterface
{
    public function parse(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        $context->text = Text::brsToPs($context->text);

        return $context;
    }
}
