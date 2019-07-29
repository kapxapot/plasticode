<?php

namespace Plasticode\Models;

use Plasticode\Query;
use Plasticode\Models\Interfaces\LinkableInterface;

class MenuItem extends DbModel implements LinkableInterface
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

    public function url() : ?string
    {
        return self::$linker->abs($this->link);
    }
}
