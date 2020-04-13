<?php

namespace Plasticode\Models\Traits;

use Plasticode\Collections\TagLinkCollection;
Use Plasticode\Util\Strings;

trait Tags
{
    protected function getTagsField() : string
    {
        return 'tags';
    }

    /**
     * Returns tags as an array of TRIMMED strings.
     * 
     * @return string[]
     */
    public function getTags() : array
    {
        $tagsField = $this->getTagsField();
        $tags = $this->{$tagsField};

        return Strings::explode($tags);
    }

    /**
     * Returns tag links.
     */
    public function tagLinks() : TagLinkCollection
    {
        return $this->getWithProperty('tagLinks');
    }

    /**
     * Adds tag links.
     */
    public function withTagLinks(TagLinkCollection $tagLinks) : self
    {
        return $this->setWithProperty('tagLinks', $tagLinks);
    }
}
