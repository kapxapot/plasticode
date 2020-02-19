<?php

namespace Plasticode\Parsing\TagMappers;

use Plasticode\IO\Image;
use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\ViewContext;
use Plasticode\Util\Strings;
use Plasticode\ViewModels\ImageViewModel;

class ImageMapper implements TagMapperInterface
{
    public function map(TagNode $tagNode, ?ParsingContext $context = null) : ViewContext
    {
        if ($context) {
            /** @var ParsingContext $context */
            $context = clone $context;
        }

        $width = 0;
        $height = 0;

        $source = $tagNode->text();
        $attrs = $tagNode->attributes();

        /** @var string|null */
        $thumb = null;

        /** @var string|null */
        $url = null;

        /** @var string|null */
        $alt = null;

        foreach ($attrs as $attr) {
            if (is_numeric($attr)) {
                if ($width == 0) {
                    $width = $attr;
                } else {
                    $height = $attr;
                }

                continue;
            }

            if (Image::isImagePath($attr)) {
                $thumb = $attr;
                continue;
            }

            if (Strings::isUrlOrRelative($attr)) {
                $url = $attr;
                continue;
            }
            
            $alt = $attr;
        }
        
        if ($context) {
            if (strlen($source) > 0) {
                $context->addLargeImage($source);
            }

            if (strlen($thumb) > 0) {
                $context->addImage($thumb);
            }
        }

        $model = new ImageViewModel(
            $tagNode->tag(),
            $source,
            $thumb,
            $alt,
            $width,
            $height,
            $url
        );

        return new ViewContext($model, $context);
    }
}
