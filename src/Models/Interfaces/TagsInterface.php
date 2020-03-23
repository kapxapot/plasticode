<?php

namespace Plasticode\Models\Interfaces;

interface TagsInterface
{
    /**
     * Returns tags as an array of TRIMMED strings.
     * 
     * @return string[]
     */
    function getTags() : array;

    /**
     * Adds tag links.
     *
     * @param TagLink[] $tagLinks
     * @return self
     */
    function withTagLinks(array $tagLinks) : self;

    /**
     * Returns tag links.
     *
     * @return TagLink[]|null
     */
    public function tagLinks() : ?array;
}
