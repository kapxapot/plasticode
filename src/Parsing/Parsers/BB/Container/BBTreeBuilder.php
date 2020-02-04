<?php

namespace Plasticode\Parsing\Parsers\BB\Container;

use Plasticode\Collection;
use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\EndElement;
use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\SequenceElement;
use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\StartElement;

class BBTreeBuilder
{
    /**
     * Builds container tree based on parts sequence.
     * 
     * @param SequenceElement[] $sequence
     * @return array
     */
    public function build(array $sequence) : array
    {
        $tree = [];
        $nodes = Collection::makeEmpty();
        
        $consume = function ($part) use (&$nodes, &$tree) {
            /** @var BBNode */
            $node = $nodes->last();

            if (!is_null($node)) {
                $node->addContent($part);
            } else {
                $tree[] = $part;
            }
        };

        foreach ($sequence as $element) {
            if ($element instanceof StartElement) {
                $node = $element->toBBNode();
                $nodes = $nodes->add($node);

                continue;
            }
            
            if ($element instanceof EndElement) {
                // matching node?
                /** @var BBNode */
                $node = $nodes->last();

                // matching node - wrap it up
                if (!is_null($node) && $node->tag == $element->tag) {
                    $nodes = $nodes->trimEnd(1);
                    $consume($node);
                }
                
                continue;
            }

            $consume($element->text);
        }

        while ($nodes->any()) {
            /** @var BBNode */
            $node = $nodes->last();
            $nodes = $nodes->trimEnd(1);

            $consume($node->text);

            foreach ($node->content as $content) {
                $consume($content);
            }
        }
        
        return $tree;
    }
}
