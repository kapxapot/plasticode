<?php

namespace Plasticode\Parsing\Interfaces;

use Plasticode\Parsing\ParsingContext;

interface ParsingStepInterface
{
    public function parse(ParsingContext $data) : ParsingContext;
}
