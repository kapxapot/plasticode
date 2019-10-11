<?php

namespace Plasticode\Models\Traits;

use Plasticode\Collection;
use Plasticode\Query;

trait Children
{
    public function parent()
    {
        return $this->lazy(function () {
            return self::get($this->parentId);
        });
    }
    
    public function children() : Collection
    {
        return $this->lazy(function () {
            return self::query()
                ->where('parent_id', $this->id)
                ->all();
        });
    }
    
    public function orphans() : Collection
    {
        return self::query()
            ->whereNull('parent_id')
            ->all();
    }
}
