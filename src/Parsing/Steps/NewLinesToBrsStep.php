<?php

namespace Plasticode\Parsing\Steps;

use Plasticode\Parsing\ParsingContext;
use Plasticode\Util\Text;

class NewLinesToBrsStep extends BaseStep
{
    public function parseContext(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        $context->text = Text::newLinesToBrs($context->text);
        
        return $context;
    }
}
