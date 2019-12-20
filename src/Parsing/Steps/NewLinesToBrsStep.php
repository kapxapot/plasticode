<?php

namespace Plasticode\Parsing\Steps;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Util\Text;

class NewLinesToBrsStep implements ParsingStepInterface
{
    public function parse(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        $context->text = Text::newLinesToBrs($context->text);
        
        return $context;
    }
}
