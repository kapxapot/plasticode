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
    protected string $tagsField = 'tags';
    protected string $tagLinksPropertyName = 'tagLinks';

    /**
     * Returns tags as an array of TRIMMED strings.
     * 
     * @return string[]
     */
    public function getTags() : array
    {
        $tagsField = $this->tagsField;
        $tags = $this->{$tagsField};

        return Strings::explode($tags);
    }
}
