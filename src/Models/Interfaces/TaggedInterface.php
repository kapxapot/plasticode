<?php

namespace Plasticode\Models\Interfaces;

use Plasticode\Collections\TagLinkCollection;

interface TaggedInterface
{
    /**
     * Returns tags as an array of TRIMMED strings.
     * 
     * @return string[]
     */
    function getTags() : array;

    /**
     * Adds tag links.
     */
    function withTagLinks(TagLinkCollection $tagLinks) : self;

    /**
     * Returns tag links.
     */
    public function tagLinks() : TagLinkCollection;
}
