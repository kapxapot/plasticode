<?php

namespace Plasticode\Parsing\Mappers;

use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\ViewContext;
use Plasticode\ViewModels\UrlViewModel;

class UrlMapper implements TagMapperInterface
{
    public function map(TagNode $tagNode, ?ParsingContext $context = null) : ViewContext
    {
        $text = $tagNode->text();
        $url = $tagNode->firstAttribute() ?? $text;

        $model = new UrlViewModel($url, $text);

        return new ViewContext($model, $context);
    }
}
