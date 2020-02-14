<?php

namespace Plasticode\Parsing\Parsers\BB;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\TagMapperSourceInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\Parsers\BB\Traits\BBAttributeParser;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\BaseStep;
use Plasticode\Util\Strings;
use Plasticode\Util\Text;

class BBParser extends BaseStep
{
    use BBAttributeParser;

    /** @var TagMapperSourceInterface */
    private $config;

    /** @var RendererInterface */
    private $renderer;

    public function __construct(TagMapperSourceInterface $config, RendererInterface $renderer)
    {
        $this->config = $config;
        $this->renderer = $renderer;
    }

    public function parseContext(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        $tags = $this->config->getTags();

        foreach ($tags as $tag) {
            $context = $this->parseTag($tag, $context);
        }

        return $context;
    }

    protected function parseImgBB(array $result, string $tag) : array
    {
        $text = $result['text'];
        
        $result['text'] = $this->parseTag(
            $text,
            $tag,
            function ($content, $attrs) use (&$result, $tag) {
                $width = 0;
                $height = 0;

                foreach ($attrs as $attr) {
                    if (is_numeric($attr)) {
                        if ($width == 0) {
                            $width = $attr;
                        } else {
                            $height = $attr;
                        }
                    } elseif (Strings::isUrlOrRelative($attr)) {
                        if (Image::isImagePath($attr)) {
                            $thumb = $attr;
                        } else {
                            $url = $attr;
                        }
                    } else {
                        $alt = $attr;
                    }
                }
                
                $result['images'][] = $thumb ?? $content; // change this to only thumb?
                $result['large_images'][] = $content;

                return [
                    'tag' => $tag,
                    'source' => $content,
                    'thumb' => $thumb,
                    'alt' => $alt,
                    'width' => $width,
                    'height' => $height,
                    'url' => $url,
                ];
            },
            null,
            'image'
        );
        
        return $result;
    }

    protected function parseCarousel(array $result) : array
    {
        $text = $result['text'];
        
        $result['text'] = $this->parseTag(
            $text,
            'carousel',
            function ($content, $attrs) use (&$result) {
                $slides = [];

                $http = '(?:https?:)?\/\/';
                
                $parts = preg_split(
                    "/({$http}[^ <]+)/is", $content, -1,
                    PREG_SPLIT_DELIM_CAPTURE
                );
                
                $parts = array_map(
                    function ($part) {
                        return trim(Text::trimBrs($part));
                    },
                    $parts
                );
                
                $parts = array_filter($parts);
                
                $slide = [];

                while (!empty($parts)) {
                    $part = array_shift($parts);
                    
                    if (preg_match("/^{$http}\S+$/", $part, $matches)) {
                        if ($slide) {
                            $slides[] = $slide;
                        }
                        
                        $slide = ['src' => $part];
                        $result['large_images'][] = $part;
                    } else {
                        $slide['caption'] = $part;
                    }
                }
                
                if ($slide) {
                    $slides[] = $slide;
                }

                return [
                    'id' => Numbers::generate(10),
                    'slides' => $slides,
                ];
            }
        );
        
        return $result;
    }

    protected function parseYoutubeBB(array $result) : array
    {
        $text = $result['text'];

        $result['text'] = $this->parseTag(
            $text,
            'youtube',
            function ($content, $attrs) use (&$result) {
                if (count($attrs) > 1) {
                    $width = $attrs[0];
                    $height = $attrs[1];
                }

                $result['videos'][] = $this->linker->youtube($content);

                return [
                    'code' => $content,
                    'width' => $width ?? 0,
                    'height' => $height ?? 0,
                ];
            }
        );

        return $result;
    }
    
    private function parseTag(string $tag, ParsingContext $context) : ParsingContext
    {
        $mapper = $this->config->getMapper($tag);
        $componentName = $this->config->getComponentName($tag);

        $context->text = preg_replace_callback(
            $this->getTagPattern($tag),
            function ($matches) use ($tag, $mapper, $componentName, $context) {
                $tagNode = $this->parseTagMatches($tag, $matches);
                $viewContext = $mapper->map($tagNode, $context);
                $context = $viewContext->context();

                return $this->renderer->component(
                    $componentName,
                    $viewContext->model()
                );
            },
            $context->text
        );

        return $context;
    }
    
    private function getTagPattern(string $tag) : string
    {
        return "/\[{$tag}([^\[]*)\](.*)\[\/{$tag}\]/Uis";
    }
    
    private function parseTagMatches(string $tag, array $matches) : TagNode
    {
        /** @var string[] */
        $attrs = [];
        $content = '';

        if (count($matches) > 1) {
            $attrs = $this->parseAttributes($matches[1]);
        }

        if (count($matches) > 2) {
            $content = Text::trimBrs($matches[2]);
        }
        
        return new TagNode($tag, $attrs, $content);
    }
}
