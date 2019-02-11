<?php

namespace Plasticode\Models;

class MenuItem extends DbModel
{
    protected static $sortOrder = [
        [ 'field' => 'position' ],
        [ 'field' => 'text' ]
    ];
    
    protected static $parentIdField = 'menu_id';
    
    // GETTERS - MANY
    
    public static function getAllByMenu($menuId)
    {
        return self::getAllByField(static::$parentIdField, $menuId);
    }
}
