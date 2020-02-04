<?php

namespace Plasticode\Parsing\Parsers\BB\Container\Nodes;

/**
 * Text node.
 */
class Node
{
    /** @var string */
    public $text;

    /**
     * Creates Node.
     *
     * @param string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }
}
