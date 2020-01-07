<?php

namespace Plasticode\Parsing\Steps;

use Plasticode\Parsing\ParsingContext;
use Plasticode\Util\Text;

class BrsToPsStep extends BaseStep
{
    public function parseContext(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        $context->text = Text::brsToPs($context->text);

        return $context;
    }
}
