<?php

namespace Plasticode\Config\Parsing\Interfaces;

interface ReplacesConfigInterface
{
    /**
     * Returns replaces for final markup cleanup.
     */
    function getCleanupReplaces() : array;

    /**
     * Returns replaces for fixed parsing A -> B.
     */
    function getReplaces() : array;
}
