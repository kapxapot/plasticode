<?php

namespace Plasticode\Models;

use Plasticode\Query;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Util\SortStep;

class MenuItem extends DbModel implements LinkableInterface
{
    protected static $parentIdField = 'menu_id';

    /** @return \Plasticode\Util\SortStep[] */
    protected static function getSortOrder() : array
    {
        return [
            SortStep::create('position'),
            SortStep::create('text')
        ];
    }
    
    // queries
    
    public static function getByMenu($menuId) : Query
    {
        return self::query()
            ->where(static::$parentIdField, $menuId);
    }

    public function url() : ?string
    {
        return self::$linker->rel($this->link);
    }
}
