<?php

namespace Plasticode\Parsing\Mappers;

use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\ViewContext;
use Plasticode\Util\Arrays;
use Plasticode\ViewModels\LinkViewModel;

class UrlMapper implements TagMapperInterface
{
    public function map(TagNode $tagNode, ?ParsingContext $context = null) : ViewContext
    {
        $content = $tagNode->text();
        $url = $tagNode->firstAttribute() ?? $content;

        $model = new LinkViewModel($url, $content);

        return new ViewContext($model, $context);
    }
}
