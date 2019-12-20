<?php

namespace Plasticode\Parsing\Mappers;

use Plasticode\Parsing\Interfaces\MapperInterface;
use Plasticode\Util\Text;

class ListMapper implements MapperInterface
{
    public function map(string $content, array $attrs) : array
    {
        $ordered = !empty($attrs);
        $content = strstr($content, '[*]');
        
        if ($content !== false) {
            $items = preg_split('/\[\*\]/', $content, -1, PREG_SPLIT_NO_EMPTY);
            
            $items = array_map(
                function ($item) {
                    return Text::trimBrs($item);
                },
                $items
            );
        }
        
        return [
            'ordered' => $ordered,
            'items' => $items ?? [],
        ];
    }
}
