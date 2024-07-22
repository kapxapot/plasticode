<?php

namespace Plasticode\Models\Interfaces;

interface TaggedInterface extends DbModelInterface
{
    /**
     * Returns tags as an array of TRIMMED strings.
     *
     * @return string[]
     */
    public function getTags(): array;
}
