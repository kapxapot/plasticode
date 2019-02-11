<?php

namespace Plasticode\Models\Traits;

trait Children
{
	public function parent()
	{
	    return $this->lazy(__FUNCTION__, function () {
    	    return self::get($this->parentId);
	    });
	}
	
	public function children()
	{
	    return $this->lazy(__FUNCTION__, function () {
	        return self::getManyByField('parent_id', $this->id);
	    });
	}
}
