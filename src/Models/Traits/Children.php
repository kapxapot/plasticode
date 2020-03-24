<?php

namespace Plasticode\Models\Traits;

use Plasticode\Collection;

trait Children
{
    protected ?self $parent = null;
    protected ?Collection $children = null;

    public function parent() : ?self
    {
        return $this->parent;
    }

    public function withParent(?self $parent) : self
    {
        $this->parent = $parent;
        return $this;
    }
    
    public function children() : Collection
    {
        return $this->children;
    }

    public function withChildren(Collection $children) : self
    {
        $this->children = $children;
        return $this;
    }
}
