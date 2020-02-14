<?php

namespace Plasticode\Parsing\Mappers;

use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Util\Strings;
use Plasticode\ViewModels\ViewModel;

class QuoteMapper implements TagMapperInterface
{
    public function map(TagNode $tagNode) : ViewModel
    {
        $author = null;
        $url = null;
        $chunks = [];

        foreach ($tagNode->attributes as $attr) {
            if (Strings::isUrl($attr)) {
                $url = $attr;
                continue;
            }
            
            if (!$author) {
                $author = $attr;
                continue;
            }
            
            $chunks[] = $attr;
        }
        
        return QuoteViewModel(
            [
            'text' => $tagNode->text,
            'author' => $author,
            'url' => $url,
            'chunks' => $chunks,
        ];
    }
}
