<?php

namespace Plasticode\Parsing\TagMappers;

use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\ViewContext;
use Plasticode\Util\Numbers;
use Plasticode\ViewModels\SpoilerViewModel;

class SpoilerMapper implements TagMapperInterface
{
    public function map(TagNode $tagNode, ?ParsingContext $context = null) : ViewContext
    {
        $model = new SpoilerViewModel(
            Numbers::generate(10),
            $tagNode->text(),
            $tagNode->firstAttribute()
        );

        return new ViewContext($model, $context);
    }
}
