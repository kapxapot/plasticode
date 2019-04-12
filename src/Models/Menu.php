<?php

namespace Plasticode\Models;

class Menu extends DbModel
{
    protected static $sortField = 'position';

    // PROPS
    
    public function items()
    {
        return self::$menuItemRepository
            ->getByMenu($this->id)
            ->all();
    }
}
