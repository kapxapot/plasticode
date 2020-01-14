<?php

namespace Plasticode\Parsing\Parsers\BB;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Util\Arrays;
use Plasticode\Util\Text;

class BBContainerTree
{
    /** @var BBContainer */
    private $container;

    /** @var RendererInterface */
    protected $renderer;

    public function __construct(BBContainer $container, RendererInterface $renderer)
    {
        $this->container = $container;
        $this->renderer = $renderer;
    }

    public function parse(string $text) : string
    {
        $tree = $this->build($text);
        return $this->render($tree);
    }

    protected function build(string $text) : array
    {
        $tree = [];
        
        $ctags = $this->container->getTags();
        
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
                    $attrs = $this->container->parseAttributes($matches[2]);
                    
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

    protected function render(?array $tree) : string
    {
        if (is_null($tree)) {
            return '';
        }

        $parts = [];

        foreach ($tree as $node) {
            if (is_array($node)) {
                $node['text'] = $this->render($node['content']);
                $parts[] = $this->container->renderNode($node);
            } else {
                $parts[] = $this->renderer->text($node);
            }
        }
        
        return implode(Text::BrBr, $parts);
    }
}
