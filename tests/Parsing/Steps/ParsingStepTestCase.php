<?php

namespace Plasticode\Tests\Parsing\Steps;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Tests\Parsing\BaseParsingRenderTestCase;

abstract class ParsingStepTestCase extends BaseParsingRenderTestCase
{
    protected function parse(string $text) : ParsingContext
    {
        return $this->step()->parse($text);
    }

    protected function parseLines(array $lines) : ParsingContext
    {
        $context = ParsingContext::fromLines($lines);

        return $this->step()->parseContext($context);
    }

    protected abstract function step() : ParsingStepInterface;

    protected function assertContextIsImmutable() : void
    {
        $context = ParsingContext::fromText('');
        $newContext = $this->step()->parseContext($context);

        $this->assertNotSame($context, $newContext);
    }
}
