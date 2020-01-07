<?php

namespace Plasticode\Parsing\Interfaces;

use Plasticode\Parsing\ParsingContext;

interface ParsingStepInterface
{
    public function parse(?string $text) : ParsingContext;
    public function parseContext(ParsingContext $data) : ParsingContext;
}
