<?php

namespace Plasticode\Parsing;

use Plasticode\Config\Interfaces\ParsingConfigInterface;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\IO\Image;
use Plasticode\Util\Arrays;
use Plasticode\Util\Numbers;
use Plasticode\Util\Text;
use Webmozart\Assert\Assert;

class Parser
{
    /** @var \Plasticode\Config\Interfaces\ParsingConfigInterface */
    protected $config;

    /** @var \Plasticode\Core\Interfaces\RendererInterface */
    protected $renderer;

    /** @var \Plasticode\Core\Interfaces\LinkerInterface */
    protected $linker;

    public function __construct(ParsingConfigInterface $config, RendererInterface $renderer, LinkerInterface $linker)
    {
        $this->config = $config;
        $this->renderer = $renderer;
        $this->linker = $linker;
    }
    
    private const TAG_PARTS_DELIMITER = '|';
    private const BR = '<br/>';
    private const BRx2 = '<br/><br/>';

    public function joinTagParts(array $parts) : string
    {
        return implode(self::TAG_PARTS_DELIMITER, $parts);
    }
    
    /**
     * Shortcut for renderer->component.
     *
     * @param string $componentName
     * @param array|null $data
     * @return string
     */
    protected function render(string $componentName, ?array $data = null) : string
    {
        return $this->renderer->component($componentName, $data);
    }
    
    protected function getBBContainerPattern(string $name) : string
    {
        return "/\[{$name}([^\[]*)\](.*)\[\/{$name}\]/Uis";
    }
    
    protected function parseBBContainerAttributes(string $str) : array
    {
        $attrsStr = trim($str, ' |=');
        $attrs = preg_split('/\|/', $attrsStr, -1, PREG_SPLIT_NO_EMPTY);
        
        return $attrs;
    }
    
    protected function parseBBContainerMatches(array $matches) : array
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
    
    protected function parseBBContainer(string $text, string $containerName, callable $map, \Closure $enrich = null, string $componentName = null) : string
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
        if (!$full) {
            Assert::stringNotEmpty(
                $url,
                'Non-empty url required for parseCut() in short mode.'
            );
        }

        $cut = '[cut]';
        $cutpos = strpos($text, $cut);

        if ($cutpos !== false) {
            if ($full) {
                $text = str_replace($cut, '', $text);
                $text = $this->br2p($text);
            } else {
                $text = substr($text, 0, $cutpos);
                $text = Text::trimBrs($text);
                
                $text .= $this->render(
                    'read_more',
                    ['url' => $url, 'label' => $label]
                );
            }
            
            $text = $this->cleanMarkup($text);
        }

        return $text;
    }
    
    public function makeAbsolute(string $text) : string
    {
        $siteUrl = $this->linker->abs();

        $text = str_replace('=/', '=' . $siteUrl, $text);
        $text = str_replace('="/', '="' . $siteUrl, $text);
        
        return $text;
    }
    
    private function cleanMarkup(string $text) : string
    {
        $replaces = $this->config->getCleanupReplaces();

        foreach ($replaces as $key => $value) {
            $text = preg_replace('#(' . $key . ')#', $value, $text);
        }
        
        return $text;
    }
    
    private function br2p(string $text) : string
    {
        return str_replace(self::BRx2, '</p><p>', $text);
    }

    public function parse(?string $text) : ?string
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
        $text = str_replace(['\r\n', '\r', '\n'], self::BR, $text);
        
        $result['text'] = $text;

        // bb [tags]
        $result = $this->parseBracketContainers($result);
        $result = $this->parseBrackets($result);
        
        $text = $result['text'];

        // apply config replaces
        $text = $this->applyReplaces($text);

        // extend this
        $text = $this->parseMore($text);

        // all text parsed
        $text = preg_replace('#(' . self::BR . '){3,}#', self::BRx2, $text);

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
    protected function parseMore(?string $text) : ?string
    {
        return $text;
    }

    protected function parseTitles(string $text) : array
    {
        $contents = [];

        $text = Text::processLines(
            $text,
            function($lines) use (&$contents) {
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
            }
        );
        
        $contents = array_map(
            function ($item) {
                $dottedLabel = str_replace('_', '.', $item['label']);
                $item['text'] = $dottedLabel . '. ' . strip_tags($item['text']);
            
                return $item;
            },
            $contents
        );

        return [
            'text' => $text,
            'contents' => $contents,
        ];
    }
    
    protected function applyReplaces(string $text) : string
    {
        $replaces = $this->config->getReplaces();

        foreach ($replaces as $from => $to) {
            $text = str_replace($from, $to, $text);
        }

        return $text;
    }

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
    
    protected function mapQuoteBB(string $content, array $attrs) : array
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
    
    protected function mapSpoilerBB(string $content, array $attrs) : array
    {
        return [
            'id' => Numbers::generate(10),
            'title' => Arrays::first($attrs),
            'body' => $content,
        ];
    }
    
    protected function mapListBB(string $content, array $attrs) : array
    {
        $ordered = !empty($attrs);
        $content = strstr($content, '[*]');
        
        if ($content !== false) {
            $items = preg_split('/\[\*\]/', $content, -1, PREG_SPLIT_NO_EMPTY);
            
            $items = array_map(
                function ($item) {
                    return Text::trimBrs($item);
                },
                $items
            );
        }
        
        return [
            'ordered' => $ordered,
            'items' => $items ?? [],
        ];
    }

    protected function parseBracketContainers(array $result) : array
    {
        $text = $result['text'];
        $tree = $this->buildBBContainerTree($text);
        $result['text'] = $this->renderBBContainerTree($tree);

        return $result;
    }
    
    protected function renderBBContainerTree(?array $tree) : string
    {
        if (is_null($tree)) {
            return '';
        }

        $parts = [];

        foreach ($tree as $node) {
            if (is_array($node)) {
                $node['text'] = $this->renderBBContainerTree($node['content']);
                $parts[] = $this->renderBBContainer($node);
            } else {
                $parts[] = $this->renderer->text($node);
            }
        }
        
        return implode(self::BRx2, $parts);
    }
    
    protected function renderBBNode(string $componentName, array $node, callable $map) : string
    {
        return $this->render($componentName, $map($node['text'], $node['attributes']));
    }
    
    protected function renderBBContainer(array $node) : string
    {
        $tag = $node['tag'];

        Assert::true(
            $this->isKnownBBContainerTag($tag),
            'Unknown BB container \'' . $tag . '\''
        );

        $func = 'map' . ucfirst($tag) . 'BB';

        return $this->renderBBNode($tag, $node, [$this, $func]);
    }

    protected function getBBContainerTags()
    {
        return ['spoiler', 'list', 'quote'];
    }

    protected function isKnownBBContainerTag(string $tag) : bool
    {
        $ctags = $this->getBBContainerTags();

        return array_key_exists($tag, $ctags);
    }
    
    protected function buildBBContainerTree(string $text) : array
    {
        $tree = [];
        
        $ctags = $this->getBBContainerTags();
        
        if (empty($ctags)) {
            $tree[] = $text;
        } else {
            $ctagsStr = implode('|', $ctags);
            
            $parts = preg_split(
                '/(\[\/?(?:' . $ctagsStr . ')[^\[]*\])/Ui', $text, -1,
                PREG_SPLIT_DELIM_CAPTURE
            );
            
            $parts = array_map(
                function ($part) {
                    return Text::trimBrs($part);
                },
                $parts
            );
            
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

    /**
     * Parse Markdown lists.
     */
    protected function parseListMD(string $text) : string
    {
        return Text::processLines(
            $text,
            function ($lines) {
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
            }
        );
    }
    
    protected function parseMarkdown(string $text) : string
    {
        $text = $this->parseListMD($text);

        return $text;
    }

    /**
     * Override this to render placeholder links (double brackets, etc.).
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
    public function justText(?string $text) : ?string
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
