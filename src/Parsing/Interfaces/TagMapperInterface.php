<?php

namespace Plasticode\Parsing\Interfaces;

use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\ViewModels\ViewModel;

interface TagMapperInterface
{
    public function map(TagNode $tagNode) : ViewModel;
}
