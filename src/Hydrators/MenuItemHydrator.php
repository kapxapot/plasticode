<?php

namespace Plasticode\Hydrators;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;
use Plasticode\Models\MenuItem;

class MenuItemHydrator implements HydratorInterface
{
    private LinkerInterface $linker;

    public function __construct(LinkerInterface $linker)
    {
        $this->linker = $linker;
    }

    /**
     * @param MenuItem $entity
     */
    public function hydrate(DbModel $entity) : MenuItem
    {
        return $entity
            ->withUrl(
                $this->linker->rel($entity->link)
            );
    }
}
