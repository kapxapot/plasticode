<?php

namespace Plasticode\Parsing\Mappers;

use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\ViewContext;
use Plasticode\Util\Text;
use Plasticode\ViewModels\ListViewModel;

class ListMapper implements TagMapperInterface
{
    public function map(TagNode $tagNode, ?ParsingContext $context = null) : ViewContext
    {
        $ordered = !empty($tagNode->attributes());
        $items = [];

        $content = strstr($tagNode->text(), '[*]');
        
        if ($content !== false) {
            $items = preg_split('/\[\*\]/', $content, -1, PREG_SPLIT_NO_EMPTY);
            
            $items = array_map(
                function ($item) {
                    return Text::trimNewLinesAndBrs($item);
                },
                $items
            );
        }
        
        $model = new ListViewModel($items, $ordered);

        return new ViewContext($model, $context);
    }
}
