<?php

namespace Plasticode\Hydrators;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;
use Plasticode\Models\MenuItem;

class MenuItemHydrator extends Hydrator
{
    private LinkerInterface $linker;

    public function __construct(
        LinkerInterface $linker
    )
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
                fn () => $this->linker->rel($entity->link)
            );
    }
}
