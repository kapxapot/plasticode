<?php

namespace Plasticode\Parsing\Parsers\BB\Container;

use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\Parsers\BB\Nodes\Node;
use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\EndElement;
use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\SequenceElement;
use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\StartElement;
use Plasticode\Util\Arrays;

class BBTreeBuilder
{
    /**
     * Builds container tree based on parts sequence.
     * 
     * @param SequenceElement[] $sequence
     * @return Node[]
     */
    public function build(array $sequence) : array
    {
        /** @var Node[] */
        $tree = [];

        /** @var TagNode[] */
        $tagNodes = [];
        
        $consume = function (Node $node) use (&$tagNodes, &$tree) {
            if (!empty($tagNodes)) {
                Arrays::last($tagNodes)->addChild($node);
            } else {
                $tree[] = $node;
            }
        };

        foreach ($sequence as $element) {
            if ($element instanceof StartElement) {
                $tagNodes[] = $element->toTagNode();
                continue;
            }
            
            if ($element instanceof EndElement) {
                // matching node?
                /** @var TagNode */
                $tagNode = Arrays::last($tagNodes);

                // matching node - wrap it up
                if ($tagNode && $tagNode->tag() == $element->tag) {
                    array_pop($tagNodes);
                    $consume($tagNode);
                    continue;
                }
            }

            // text element or not matching end element
            $consume($element->toNode());
        }

        // there still can be some dangling nodes
        while (!empty($tagNodes)) {
            /** @var TagNode */
            $tagNode = array_pop($tagNodes);

            $consume($tagNode->reduce());

            foreach ($tagNode->children() as $child) {
                $consume($child);
            }
        }
        
        return $tree;
    }
}
