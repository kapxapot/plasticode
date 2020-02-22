<?php

namespace Plasticode\Tests\Mocks\Repositories;

use Plasticode\Collection;
use Plasticode\Models\Tag;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

class TagRepositoryMock implements TagRepositoryInterface
{
    /** @var Collection */
    private $tags;

    public function __construct()
    {
        $this->tags = Collection::make(
            [
                new Tag(
                    [
                        'id' => 1,
                        'tag' => 'warcraft',
                        'entity_type' => 'pages',
                        'entity_id' => 2
                    ]
                ),
            ]
        );
    }
    
    public function getByTag(string $tag) : Collection
    {
        return $this->tags
            ->where('tag', $tag);
    }

    public function exists(string $tag) : bool
    {
        return $this->getByTag($tag)->any();
    }
}
