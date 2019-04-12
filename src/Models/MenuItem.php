<?php

namespace Plasticode\Models;

use Plasticode\Query;

class MenuItem extends DbModel
{
    protected static $sortOrder = [
        [ 'field' => 'position' ],
        [ 'field' => 'text' ]
    ];
    
    protected static $parentIdField = 'menu_id';
    
    // queries
    
    public static function getByMenu($menuId) : Query
    {
        return self::query()
            ->where(static::$parentIdField, $menuId);
    }
}
