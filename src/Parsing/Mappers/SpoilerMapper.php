<?php

namespace Plasticode\Parsing\Mappers;

use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\ViewContext;
use Plasticode\Util\Arrays;
use Plasticode\Util\Numbers;
use Plasticode\ViewModels\SpoilerViewModel;

class SpoilerMapper implements TagMapperInterface
{
    public function map(TagNode $tagNode, ?ParsingContext $context = null) : ViewContext
    {
        $model = new SpoilerViewModel(
            Numbers::generate(10),
            $tagNode->text,
            Arrays::first($tagNode->attributes)
        );

        return new ViewContext($model, $context);
    }
}
