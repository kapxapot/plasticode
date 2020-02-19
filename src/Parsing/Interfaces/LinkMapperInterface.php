<?php

namespace Plasticode\Parsing\Interfaces;

interface LinkMapperInterface
{
    /**
     * Maps link chunks to a rendered link.
     *
     * @param string[] $chunks
     * @return string
     */
    public function map(array $chunks) : ?string;
}
