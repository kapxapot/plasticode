<?php

namespace Plasticode\Parsing\Interfaces;

use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\ViewContext;

/**
 * Maps tag node & parsing context to a view context (view model + modified parsing context).
 */
interface TagMapperInterface
{
    public function map(TagNode $tagNode, ?ParsingContext $context = null) : ViewContext;
}
