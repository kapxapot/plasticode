<?php

namespace Plasticode\Parsing\Interfaces;

use Plasticode\Parsing\ParsingContext;

interface LinkRendererInterface
{
    public function renderLinks(ParsingContext $context) : ParsingContext;
}
