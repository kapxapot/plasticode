<?php

namespace Plasticode\Models;

use Plasticode\Collection;
use Plasticode\Models\Interfaces\LinkableInterface;

class Menu extends DbModel implements LinkableInterface
{
    protected static $sortField = 'position';

    // PROPS
    
    public function items() : Collection
    {
        return self::$menuItemRepository
            ->getByMenu($this->id)
            ->all();
    }

    public function url() : ?string
    {
        return self::$linker->rel($this->link);
    }
}
