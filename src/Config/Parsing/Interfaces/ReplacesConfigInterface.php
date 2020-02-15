<?php

namespace Plasticode\Config\Parsing\Interfaces;

interface ReplacesConfigInterface
{
    /**
     * Returns replaces for final markup cleanup.
     *
     * @return array
     */
    public function getCleanupReplaces() : array;

    /**
     * Returns replaces for fixed parsing A -> B.
     *
     * @return array
     */
    public function getReplaces() : array;
}
