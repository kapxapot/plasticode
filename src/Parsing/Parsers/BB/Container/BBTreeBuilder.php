<?php

namespace Plasticode\Parsing\Parsers\BB\Container;

use Plasticode\Collection;

class BBTreeBuilder
{
    /**
     * Builds container tree based on parts sequence.
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

        foreach ($sequence as $part) {
            if (!is_array($part)) {
                $consume($part);
            }

            switch ($part['type']) {
                case 'start':
                    $node = new BBNode(
                        $part['tag'],
                        $part['attributes'],
                        $part['text']
                    );

                    $nodes = $nodes->add($node);

                    break;
                
                case 'end':
                    // matching node?
                    /** @var BBNode */
                    $node = $nodes->last();

                    // matching node - wrap it up
                    if (!is_null($node) && $node->tag == $part['tag']) {
                        $nodes = $nodes->trimEnd(1);
                        $consume($node);
                    } else {
                        $consume($part['text']);
                    }

                    break;
            }
        }

        while ($nodes->any()) {
            /** @var BBNode */
            $node = $nodes->last();
            $nodes = $nodes->trimEnd(1);

            $consume($node->text);

            foreach ($node->content as $part) {
                $consume($part);
            }
        }
        
        return $tree;
    }
}
