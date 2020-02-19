<?php

namespace Plasticode\Parsing\TagMappers;

use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\ViewContext;
use Plasticode\ViewModels\ColorViewModel;

class ColorMapper implements TagMapperInterface
{
    public function map(TagNode $tagNode, ?ParsingContext $context = null) : ViewContext
    {
        $model = new ColorViewModel(
            $tagNode->text(),
            $tagNode->firstAttribute()
        );

        return new ViewContext($model, $context);
    }
}
