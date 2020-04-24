<?php

namespace Plasticode\Models\Traits;

use Plasticode\Collections\TagLinkCollection;
Use Plasticode\Util\Strings;

/**
 * @method TagLinkCollection tagLinks()
 * @method static withTagLinks(TagLinkCollection|callable $tagLinks)
 */
trait Tagged
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
}
