<?php

namespace Plasticode\Models;

use Plasticode\Collection;
use Plasticode\Models\Interfaces\LinkableInterface;

class Menu extends DbModel implements LinkableInterface
{
    /**
     * Todo: move this to repo
     */
    protected static $sortField = 'position';
    
    /**
     * Todo: move this to mapper
     *
     * @return Collection
     */
    public function items() : Collection
    {
        return self::$menuItemRepository
            ->getByMenu($this->id);
    }

    /**
     * Todo: move this to view model
     *
     * @return string|null
     */
    public function url() : ?string
    {
        return self::$linker->rel($this->link);
    }
}
