<?php

namespace Plasticode\Parsing\Mappers;

use Plasticode\Parsing\CarouselSlide;
use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\ViewContext;
use Plasticode\Util\Numbers;
use Plasticode\Util\Text;
use Plasticode\ViewModels\CarouselViewModel;

class CarouselMapper implements TagMapperInterface
{
    public function map(TagNode $tagNode, ?ParsingContext $context = null) : ViewContext
    {
        if ($context) {
            $context = clone $context;
        }

        /** @var CarouselSlide[] */
        $slides = [];

        $http = '(?:https?:)?\/\/';
        
        $parts = preg_split(
            "/({$http}[^ <]+)/is",
            $tagNode->text(),
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );
        
        $parts = array_map(
            function ($part) {
                return trim(Text::trimNewLinesAndBrs($part));
            },
            $parts
        );
        
        $parts = array_filter($parts);
        
        /** @var CarouselSlide */
        $slide = null;

        while (!empty($parts)) {
            $part = array_shift($parts);
            
            if (preg_match("/^{$http}\S+$/", $part, $matches)) {
                if ($slide) {
                    $slides[] = $slide;
                }
                
                $slide = new CarouselSlide($part);

                if ($context) {
                    $context->addLargeImage($part);
                }

                continue;
            }
            
            if ($slide) {
                $slide->setCaption($part);
            }
        }
        
        if ($slide) {
            $slides[] = $slide;
        }

        $model = new CarouselViewModel(
            Numbers::generate(10),
            $slides
        );

        return new ViewContext($model, $context);
    }
}
