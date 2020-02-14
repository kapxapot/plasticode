<?php

namespace Plasticode\Parsing\Mappers;

use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Util\Arrays;
use Plasticode\Util\Numbers;
use Plasticode\ViewModels\SpoilerViewModel;
use Plasticode\ViewModels\ViewModel;

class SpoilerMapper implements TagMapperInterface
{
    public function map(TagNode $tagNode) : ViewModel
    {
        return new SpoilerViewModel(
            Numbers::generate(10),
            $tagNode->text,
            Arrays::first($tagNode->attributes)
        );
    }
}
