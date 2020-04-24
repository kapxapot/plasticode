<?php

namespace Plasticode\Models\Interfaces;

interface TaggedInterface
{
    /**
     * Returns tags as an array of TRIMMED strings.
     * 
     * @return string[]
     */
    function getTags() : array;

    /**
     * Tab name for TagLink & UI.
     */
    function tabName() : string;
}
