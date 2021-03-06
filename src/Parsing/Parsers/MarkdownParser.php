<?php

namespace Plasticode\Parsing\Parsers;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\BaseStep;

class MarkdownParser extends BaseStep
{
    /** @var RendererInterface */
    private $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function parseContext(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        $lines = $context->getLines();
        $lines = $this->parseLists($lines);
        
        $context->setLines($lines);
        
        return $context;
    }

    /**
     * Parses lists.
     * 
     * @param string[] $lines
     * @return string[]
     */
    protected function parseLists(array $lines) : array
    {
        $results = [];
        $list = [];
        $ordered = null;

        $flush = function () use (&$list, &$ordered, &$results) {
            if (empty($list)) {
                return;
            }

            $results[] = $this->renderer->component(
                'list',
                [
                    'items' => $list,
                    'ordered' => $ordered
                ]
            );

            $list = [];
            $ordered = null;
        };
        
        foreach ($lines as $line) {
            if (preg_match('/^(\*|-|\+|(\d+)\.)\s+(.*)$/', trim($line), $matches)) {
                $itemOrdered = strlen($matches[2]) > 0;

                if (!empty($list) && $ordered !== $itemOrdered) {
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
}
