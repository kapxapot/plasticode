<?php

namespace Plasticode\Parsing;

class BBParser
{
    protected function parseUrlBB(string $text) : string
    {
        return $this->parseBBContainer(
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
        
        $result['text'] = $this->parseBBContainer(
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
                    } elseif (strpos($attr, 'http') === 0 || strpos($attr, '/') === 0) {
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
        
        $result['text'] = $this->parseBBContainer(
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
        return $this->parseBBContainer(
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

        $result['text'] = $this->parseBBContainer(
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
}
