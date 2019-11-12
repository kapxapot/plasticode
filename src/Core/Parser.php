<?php

namespace Plasticode\Core;

use Plasticode\Contained;
use Plasticode\Exceptions\InvalidArgumentException;
use Plasticode\IO\Image;
use Plasticode\Util\Arrays;
use Plasticode\Util\Numbers;
use Plasticode\Util\Text;

class Parser extends Contained
{
    private $config;

    public function __construct($container, $config)
    {
        parent::__construct($container);
        $this->config = $config;
    }
    
    const TAG_PARTS_DELIMITER = '|';

    public function joinTagParts(array $parts) : string
    {
        return implode(self::TAG_PARTS_DELIMITER, $parts);
    }
    
    protected function render($componentName, $data = null)
    {
        return $this->renderer->component($componentName, $data);
    }
    
    protected function getBBContainerPattern($name)
    {
        return "/\[{$name}([^\[]*)\](.*)\[\/{$name}\]/Uis";
    }
    
    protected function parseBBContainerAttributes($str)
    {
        $attrsStr = trim($str, ' |=');
        $attrs = preg_split('/\|/', $attrsStr, -1, PREG_SPLIT_NO_EMPTY);
        
        return $attrs;
    }
    
    protected function parseBBContainerMatches($matches)
    {
        if (!empty($matches)) {
            $content = Text::trimBrs($matches[2]);
            $attrs = $this->parseBBContainerAttributes($matches[1]);
        }
            
        return [
            'content' => $content ?? 'parse error',
            'attrs' => $attrs ?? [],
        ];
    }
    
    protected function parseBBContainer($text, $containerName, \Closure $map, \Closure $enrich = null, $componentName = null)
    {
        $componentName = $componentName ?? $containerName;
        
        return preg_replace_callback(
            $this->getBBContainerPattern($containerName),
            function ($matches) use ($componentName, $map, $enrich) {
                $parsed = $this->parseBBContainerMatches($matches);
                $data = $map($parsed['content'], $parsed['attrs']);
                
                if ($enrich) {
                    $data = $enrich($data);
                }

                return $this->render($componentName, $data);
            },
            $text
        );
    }
    
    public function parseCut(string $text, string $url = null, bool $full = true, string $label = null) : string
    {
        $cut = '[cut]';
        $cutpos = strpos($text, $cut);

        if ($cutpos !== false) {
            if ($full === false) {
                $text = substr($text, 0, $cutpos);
                $text = Text::trimBrs($text);
                
                if (strlen($url) == 0) {
                    throw new InvalidArgumentException(
                        'Non-empty url required for parseCut() in short mode.'
                    );
                }

                $text .= $this->render('read_more', ['url' => $url, 'label' => $label]);
            }
            else {
                $text = str_replace($cut, '', $text);
                $text = $this->br2p($text);
            }
            
            $text = $this->cleanMarkup($text);
        }

        return $text;
    }
    
    public function makeAbsolute($text)
    {
        $siteUrl = $this->linker->abs();

        $text = str_replace('=/', '=' . $siteUrl, $text);
        $text = str_replace('="/', '="' . $siteUrl, $text);
        
        return $text;
    }

    /**
     * Вырезает из текста теги [tag][/tag].
     * 
     * Не используется?
     */
    public function stripTags($text) : string
    {
        return preg_replace('/\[(.*)\](.*)\[\/(.*)\]/U', '\$2', $text);
    }
    
    private function cleanMarkup($text) : string
    {
        $replaces = $this->config->getCleanupReplaces();

        foreach ($replaces as $key => $value) {
            $text = preg_replace('#(' . $key . ')#', $value, $text);
        }
        
        return $text;
    }
    
    private function br2p($text)
    {
        return str_replace('<br/><br/>', '</p><p>', $text);
    }

    public function parse($text)
    {
        if (strlen($text) == 0) {
            return null;
        }

        // titles
        // !! before linebreaks replacement !!
        $result = $this->parseTitles($text);
        $text = $result['text'];

        // markdown
        // !! before linebreaks replacement !!
        $text = $this->parseMarkdown($text);

        // \n -> br -> p
        $text = str_replace(['\r\n', '\r', '\n'], '<br/>', $text);
        
        $result['text'] = $text;

        // bb [tags]
        $result = $this->parseBracketContainers($result);
        $result = $this->parseBrackets($result);
        
        $text = $result['text'];

        // config replaces
        $text = $this->replaces($text);

        // extend this
        $text = $this->parseMore($text);

        // all text parsed
        $text = preg_replace('#(<br/>){3,}#', '<br/><br/>', $text);
        $text = '<p>' . $this->br2p($text) . '</p>';

        $result['text'] = $this->cleanMarkup($text);
        
        // set proxy image fields
        $result['large_image'] = Arrays::first($result['large_images'] ?? []);
        $result['image'] = Arrays::first($result['images'] ?? []);
        $result['video'] = Arrays::first($result['videos'] ?? []);

        return $result;
    }
    
    /**
     * Override this for additional parsing. Double brackets etc.
     */
    protected function parseMore($text)
    {
        return $text;
    }

    protected function parseTitles($text)
    {
        $contents = [];

        $text = Text::processLines($text, function($lines) use (&$contents) {
            $results = [];
            $count = [];
            
            $min = 2;
            $max = 6;

            for ($i = $min; $i <= $max; $i++) {
                $count[$i] = 0;
            }

            foreach ($lines as $line) {
                $line = trim($line);
                
                if (strlen($line) > 0) {
                    $r = '{' . $min . ',' . $max . '}';
                    $line = preg_replace_callback(
                        '/^(\|' . $r . '|#' . $r . '\s+)(.*)$/',
                        function($matches) use (&$contents, &$count, $min, $max) {
                            $sticks = trim($matches[1]);
                            $content = trim($matches[2], ' |');
                            
                            $withContents = true;

                            if (substr($content, -1) == '#') {
                                $withContents = false;
                                $content = rtrim($content, '#');
                            }
                            
                            // parse
                            $tempResult = $this->parseBrackets(['text' => $content]); // render [ ]
                            $content = $tempResult['text'];

                            $content = $this->parseMore($content); // render [[ ]]
                            
                            $level = strlen($sticks);
                            $label = null;

                            if ($withContents) {
                                $count[$level]++;
                                
                                for ($i = $level + 1; $i <= $max; $i++) {
                                    $count[$i] = 0;
                                }

                                $label = implode('_', array_slice($count, 0, $level - $min + 1));
                            }

                            $subtitle = [
                                'level' => $level - 1,
                                'label' => $label,
                                'text' => $content,
                            ];

                            if ($withContents) {
                                $contents[] = $subtitle;
                            }
    
                            return $this->render('subtitle', $subtitle);
                        },
                        $line
                    );
                }
    
                $results[] = $line;
            }
            
            return $results;
        });
        
        $contents = array_map(function ($item) {
            $item['text'] = str_replace('_', '.', $item['label']) . '. ' . strip_tags($item['text']);
            return $item;
        }, $contents);

        return [
            'text' => $text,
            'contents' => $contents,
        ];
    }
    
    protected function replaces($text)
    {
        $replaces = $this->config->getReplaces();

        foreach ($replaces as $from => $to) {
            $text = str_replace($from, $to, $text);
        }

        return $text;
    }

    protected function parseUrlBB($text)
    {
        return $this->parseBBContainer($text, 'url', function ($content, $attrs) {
            return [
                'url' => Arrays::first($attrs) ?? $content,
                'text' => $content,
            ];
        });
    }

    protected function parseImgBB($result, $tag)
    {
        $text = $result['text'];
        
        $result['text'] = $this->parseBBContainer($result['text'], $tag, function ($content, $attrs) use (&$result, $tag) {
            $width = 0;
            $height = 0;

            foreach ($attrs as $attr) {
                if (is_numeric($attr)) {
                    if ($width == 0) {
                        $width = $attr;
                    } else {
                        $height = $attr;
                    }
                }
                elseif (strpos($attr, 'http') === 0 || strpos($attr, '/') === 0) {
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
        }, null, 'image');
        
        return $result;
    }

    protected function parseCarousel($result)
    {
        $text = $result['text'];
        
        $result['text'] = $this->parseBBContainer($text, 'carousel', function ($content, $attrs) use (&$result) {
            $slides = [];

            $http = '(?:https?:)?\/\/';
            
            $parts = preg_split("/({$http}[^ <]+)/is", $content, -1, PREG_SPLIT_DELIM_CAPTURE);
            
            $parts = array_map(function ($part) {
                return trim(Text::trimBrs($part));
            }, $parts);
            
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
        });
        
        return $result;
    }
    
    private function randPic($width, $height)
    {
        return 'https://picsum.photos/' . $width . '/' . $height . '?' . Numbers::generate(6);
    }

    protected function parseColorBB($text)
    {
        return $this->parseBBContainer($text, 'color', function ($content, $attrs) {
            return [
                'content' => $content,
                'color' => Arrays::first($attrs),
            ];
        });
    }

    protected function parseQuoteBB($text, $quoteName, \Closure $enrich = null)
    {
        return $this->parseBBContainer($text, $quoteName, [$this, 'mapQuoteBB'], $enrich, 'quote');
    }
    
    protected function mapQuoteBB($content, $attrs)
    {
        $author = null;
        $chunks = [];

        foreach ($attrs as $attr) {
            if (strpos($attr, 'http') === 0) {
                $url = $attr;
            } elseif (!$author) {
                $author = $attr;
            } else {
                $chunks[] = $attr;
            }
        }
        
        return [
            'text' => $content,
            'author' => $author,
            'url' => $url,
            'chunks' => $chunks,
        ];
    }

    protected function parseYoutubeBB($result)
    {
        $text = $result['text'];

        $result['text'] = $this->parseBBContainer($text, 'youtube', function ($content, $attrs) use (&$result) {
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
        });

        return $result;
    }

    protected function parseSpoilerBB($text)
    {
        return $this->parseBBContainer($text, 'spoiler', [ $this, 'mapSpoilerBB' ]);
    }
    
    protected function mapSpoilerBB($content, $attrs)
    {
        return [
            'id' => Numbers::generate(10),
            'title' => Arrays::first($attrs),
            'body' => $content,
        ];
    }

    protected function parseListBB($text)
    {
        return $this->parseBBContainer($text, 'list', [ $this, 'mapListBB' ]);
    }
    
    protected function mapListBB($content, $attrs)
    {
        $ordered = !empty($attrs);
        $content = strstr($content, '[*]');
        
        if ($content !== false) {
            $items = preg_split('/\[\*\]/', $content, -1, PREG_SPLIT_NO_EMPTY);
            
            $items = array_map(function ($item) {
                return Text::trimBrs($item);
            }, $items);
        }
        
        return [
            'ordered' => $ordered,
            'items' => $items ?? [],
        ];
    }

    protected function parseBracketContainers($result)
    {
        $text = $result['text'];
        $tree = $this->buildBBContainerTree($text);
        $result['text'] = $this->renderBBContainerTree($tree);

        return $result;
    }
    
    protected function renderBBContainerTree($tree)
    {
        $parts = [];

        if ($tree) {
            foreach ($tree as $node) {
                if (is_array($node)) {
                    $node['text'] = $this->renderBBContainerTree($node['content']);
                    $parts[] = $this->renderBBContainer($node);
                } else {
                    $parts[] = $this->renderer->text($node);
                }
            }
        }
        
        return implode('<br/><br/>', $parts);
    }
    
    protected function renderBBNode($componentName, $node, callable $map)
    {
        return $this->render($componentName, $map($node['text'], $node['attributes']));
    }
    
    protected function renderBBContainer($node)
    {
        switch ($node['tag']) {
            case 'list':
                return $this->renderBBNode('list', $node, [ $this, 'mapListBB' ]);
            
            case 'spoiler':
                return $this->renderBBNode('spoiler', $node, [ $this, 'mapSpoilerBB' ]);
            
            case 'quote':
                return $this->renderBBNode('quote', $node, [ $this, 'mapQuoteBB' ]);
        }
    }

    protected function getBBContainerTags()
    {
        return ['spoiler', 'list', 'quote'];
    }
    
    protected function buildBBContainerTree($text)
    {
        $tree = [];
        
        $ctags = $this->getBBContainerTags();
        
        if (empty($ctags)) {
            $tree[] = $text;
        } else {
            $ctagsStr = implode('|', $ctags);
            
            $parts = preg_split('/(\[\/?(?:' . $ctagsStr . ')[^\[]*\])/Ui', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
            
            $parts = array_map(function ($part) {
                return Text::trimBrs($part);
            }, $parts);
            
            $seq = [];
            
            foreach ($parts as $part) {
                if (preg_match('/\[(' . $ctagsStr . ')([^\[]*)\]/Ui', $part, $matches)) {
                    // container start
                    $tag = $matches[1];
                    $attrs = $this->parseBBContainerAttributes($matches[2]);
                    
                    $seq[] = [
                        'type' => 'start',
                        'tag' => $tag,
                        'attributes' => $attrs,
                    ];
                } elseif (preg_match('/\[\/(' . $ctagsStr . ')\]/Ui', $part, $matches)) {
                    // container end
                    $tag = $matches[1];

                    $seq[] = [
                        'type' => 'end',
                        'tag' => $tag,
                    ];
                } elseif (strlen($part) > 0) {
                    $seq[] = $part;
                }
            }

            $consumers = [];
            
            $consume = function ($node) use (&$consumers, &$tree) {
                if (!empty($consumers)) {
                    $i = count($consumers) - 1;
                    $consumers[$i]['content'][] = $node;
                } else {
                    $tree[] = $node;
                }
            };

            foreach ($seq as $node) {
                if (is_array($node)) {
                    if ($node['type'] == 'start') {
                        $consumers[] = [
                            'tag' => $node['tag'],
                            'attributes' => $node['attributes'],
                            'content' => [],
                        ];
                    } elseif ($node['type'] == 'end') {
                        // matching consumer?
                        $consumer = Arrays::last($consumers);
                        if ($consumer && $consumer['tag'] == $node['tag']) {
                            // finish consumer
                            $food = array_pop($consumers);
                            $consume($food);
                        } else {
                            $consume($node);
                        }
                    }
                } else {
                    $consume($node);
                }
            }
        }
        
        return $tree;
    }

    protected function parseBrackets($result)
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

    /**
     * Parse Markdown lists
     */
    protected function parseListMD($text)
    {
        return Text::processLines($text, function ($lines) {
            $results = [];
            $list = [];
            $ordered = null;

            $flush = function () use (&$list, &$ordered, &$results) {
                if (count($list) > 0) {
                    $results[] = $this->render('list', [ 'ordered' => $ordered, 'items' => $list ]);
                    $list = [];
                    $ordered = null;
                }
            };
            
            foreach ($lines as $line) {
                if (preg_match('/^(\*|-|\+|(\d+)\.)\s+(.*)$/', trim($line), $matches)) {
                    $itemOrdered = strlen($matches[2]) > 0;

                    if (count($list) > 0 && $ordered !== $itemOrdered) {
                        $flush();
                    }
                    
                    $list[] = $matches[3];
                    $ordered = $itemOrdered;
                } else {
                    $flush();
                    $results[] = $line;
                }
            }
            
            $flush();

            return $results;
        });
    }
    
    protected function parseMarkdown(string $text) : string
    {
        $text = $this->parseListMD($text);

        return $text;
    }

    /**
     * Override this to render placeholder links (double brackets etc.).
     * 
     * Example:
     * $text = str_replace('%news%/', $this->linker->news(), $text);
     */
    public function renderLinks(string $text) : string
    {
        return $text;
    }
    
    /**
     * Lightweight parsing, just text.
     */
    public function justText(?string $text) : string
    {
        if (strlen($text) == 0) {
            return $text;
        }

        $parsed = $this->parse($text);
        $text = $parsed['text'];
        $text = $this->renderLinks($text);
        
        return $text;
    }
}
