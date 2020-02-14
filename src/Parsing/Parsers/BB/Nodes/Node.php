<?php

namespace Plasticode\Parsing\Parsers\BB\Nodes;

/**
 * Text node.
 */
class Node
{
    /** @var string */
    protected $text;

    /**
     * Creates Node.
     *
     * @param string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * Get text.
     *
     * @return string
     */
    public function text() : string
    {
        return $this->text;
    }

    /**
     * Set text.
     *
     * @param string $text
     * @return void
     */
    public function setText(string $text) : void
    {
        $this->text = $text;
    }
}
