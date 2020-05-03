<?php

namespace Plasticode\Models\Traits;

/**
 * @method static|null parent()
 * @method static withParent(static|callable|null $parent)
 */
trait Parented
{
    protected string $parentPropertyName = 'parent';
    protected string $childrenPropertyName = 'children';

    /**
     * The ultimate parent.
     * 
     * @return static
     */
    public function root() : self
    {
        return $this->parent()
            ? $this->parent()->root()
            : $this;
    }
}
