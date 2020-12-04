<?php

namespace Plasticode\Parsing\Interfaces;

use Plasticode\Parsing\ParsingContext;

interface ParsingStepInterface
{
    function parse(?string $text) : ParsingContext;
    function parseContext(ParsingContext $data) : ParsingContext;
}
