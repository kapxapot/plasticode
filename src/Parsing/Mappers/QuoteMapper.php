<?php

namespace Plasticode\Parsing\Mappers;

use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\ViewContext;
use Plasticode\Util\Strings;
use Plasticode\ViewModels\QuoteViewModel;

class QuoteMapper implements TagMapperInterface
{
    protected static $viewModelClass = QuoteViewModel::class;

    public function map(TagNode $tagNode, ?ParsingContext $context = null) : ViewContext
    {
        $author = null;
        $url = null;
        $chunks = [];

        foreach ($tagNode->attributes() as $attr) {
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
        
        $modelClass = static::$viewModelClass;
        $model = new $modelClass($tagNode->text(), $author, $url, $chunks);

        return new ViewContext($model, $context);
    }
}
