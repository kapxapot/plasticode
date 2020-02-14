<?php

namespace Plasticode\Parsing\Parsers\BB;

use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Parsers\BB\Traits\BBAttributeParser;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\BaseStep;
use Plasticode\Util\Arrays;
use Plasticode\Util\Strings;
use Plasticode\Util\Text;

class BBParser extends BaseStep
{
    use BBAttributeParser;

    public function parseContext(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        return $context;
    }
    
    protected function parseBrackets(array $result) : array
    {
        $result = $this->parseImgBB($result, 'img');
        $result = $this->parseImgBB($result, 'leftimg');
        $result = $this->parseImgBB($result, 'rightimg');
        $result = $this->parseCarousel($result);
        $result = $this->parseYoutubeBB($result);

        $text = $result['text'];
        $text = $this->parseColorBB($text);
        $text = $this->parseUrlBB($text);

        $result['text'] = $text;

        return $result;
    }

    protected function parseUrlBB(string $text) : string
    {
        return $this->parseTag(
            $text,
            'url',
            function ($content, $attrs) {
                return [
                    'url' => Arrays::first($attrs) ?? $content,
                    'text' => $content,
                ];
            }
        );
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

    protected function parseColorBB(string $text) : string
    {
        return $this->parseTag(
            $text,
            'color',
            function ($content, $attrs) {
                return [
                    'content' => $content,
                    'color' => Arrays::first($attrs),
                ];
            }
        );
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
    
    private function parseTag(string $text, string $tagName, TagMapperInterface $mapper, string $componentName = null) : string
    {
        $componentName = $componentName ?? $tagName;
        
        return preg_replace_callback(
            $this->getTagPattern($tagName),
            function ($matches) use ($componentName, $mapper) {
                $parsed = $this->parseTagMatches($matches);
                $data = $mapper->map($parsed['content'], $parsed['attrs']);

                return $this->renderer->component($componentName, $data);
            },
            $text
        );
    }
    
    private function getTagPattern(string $tag) : string
    {
        return "/\[{$tag}([^\[]*)\](.*)\[\/{$tag}\]/Uis";
    }
    
    private function parseTagMatches(array $matches) : array
    {
        if (!empty($matches)) {
            $content = Text::trimBrs($matches[2]);
            $attrs = $this->parseAttributes($matches[1]);
        }
        
        return [
            'content' => $content ?? 'parse error',
            'attrs' => $attrs ?? [],
        ];
    }
}
