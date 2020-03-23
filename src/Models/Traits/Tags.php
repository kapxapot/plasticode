<?php

namespace Plasticode\Models\Traits;

use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\TagLink;
use Plasticode\Util\Strings;
use Psr\Container\ContainerInterface;

/**
 * @property string $tagsField
 * @property ContainerInterface $container
 * @property TagRepositoryInterface $tagRepository
 */
trait Tags
{
    /** @var TagLink[]|null */
    protected ?array $tagLinks = null;

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
     * Adds tag links.
     *
     * @param TagLink[] $tagLinks
     * @return self
     */
    public function withTagLinks(array $tagLinks) : self
    {
        $this->tagLinks = $tagLinks;
        return $this;
    }

    /**
     * Returns tag links.
     *
     * @return TagLink[]|null
     */
    public function tagLinks() : ?array
    {
        return $this->tagLinks;
    }
}
