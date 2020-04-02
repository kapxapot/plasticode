<?php

namespace Plasticode\Hydrators;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;
use Plasticode\Models\Tag;

class TagHydrator implements HydratorInterface
{
    private LinkerInterface $linker;

    public function __construct(LinkerInterface $linker)
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
                $this->linker->tag($entity->tag)
            );
    }
}
