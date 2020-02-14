<?php

namespace Plasticode\Parsing\Mappers;

use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Util\Text;
use Plasticode\ViewModels\ListViewModel;
use Plasticode\ViewModels\ViewModel;

class ListMapper implements TagMapperInterface
{
    public function map(TagNode $tagNode) : ViewModel
    {
        $ordered = !empty($tagNode->attributes);
        $items = [];

        $content = strstr($tagNode->text, '[*]');
        
        if ($content !== false) {
            $items = preg_split('/\[\*\]/', $content, -1, PREG_SPLIT_NO_EMPTY);
            
            $items = array_map(
                function ($item) {
                    return Text::trimBrs($item);
                },
                $items
            );
        }
        
        return new ListViewModel($items, $ordered);
    }
}
