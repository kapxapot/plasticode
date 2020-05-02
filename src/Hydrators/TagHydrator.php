<?php

namespace Plasticode\Hydrators;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;
use Plasticode\Models\Tag;

class TagHydrator extends Hydrator
{
    protected LinkerInterface $linker;

    public function __construct(
        LinkerInterface $linker
    )
    {
        $this->linker = $linker;
    }

    /**
     * @param Tag $entity
     */
    public function hydrate(DbModel $entity) : Tag
    {
        return $entity
            ->withUrl(
                fn () => $this->linker->tag($entity->tag)
            );
    }
}
