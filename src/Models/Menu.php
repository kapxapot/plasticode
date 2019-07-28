<?php

namespace Plasticode\Models;

use Plasticode\Collection;

class Menu extends DbModel
{
    protected static $sortField = 'position';

    // PROPS
    
    public function items() : Collection
    {
        return self::$menuItemRepository
            ->getByMenu($this->id)
            ->all();
    }
}
