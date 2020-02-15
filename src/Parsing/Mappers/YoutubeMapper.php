<?php

namespace Plasticode\Parsing\Mappers;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\ViewContext;
use Plasticode\ViewModels\YoutubeViewModel;

class YoutubeMapper implements TagMapperInterface
{
    /** @var LinkerInterface */
    private $linker;

    public function __construct(LinkerInterface $linker)
    {
        $this->linker = $linker;
    }

    public function map(TagNode $tagNode, ?ParsingContext $context = null) : ViewContext
    {
        $width = 0;
        $height = 0;

        $code = $tagNode->text();
        $attrs = $tagNode->attributes();

        if (count($attrs) > 1) {
            $width = $attrs[0];
            $height = $attrs[1];
        }

        if ($context) {
            /** @var ParsingContext $context */
            $context = clone $context;
            
            $link = $this->linker->youtube($code);
            $context->addVideo($link);
        }

        $model = new YoutubeViewModel($code, $width, $height);

        return new ViewContext($model, $context);
    }
}
