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
    protected static function getTagsEntityType() : string
    {
        return static::getTable();
    }
    
    /**
     * Returns tags as an array of TRIMMED strings.
     * 
     * @return string[]
     */
    public function getTags() : array
    {
        $tags = $this->{static::$tagsField};
        
        return Strings::explode($tags);
    }
    
    /**
     * Returns tag links.
     *
     * @return TagLink[]
     */
    public function tagLinks() : array
    {
        $tab = static::getTagsEntityType();
        $tags = $this->getTags();
        
        return array_map(
            function ($t) use ($tab) {
                $url = self::$container->linker->tag($t, $tab);
                return new TagLink($t, $url);
            },
            $tags
        );
    }
}
