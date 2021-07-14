<?php

namespace Plasticode\Models\Traits;

/**
 * Implements {@see Plasticode\Models\Interfaces\ParentedInterface}.
 * 
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

    /**
     * :(
     */
    public function isOrphan() : bool
    {
        return !$this->hasParent();
    }

    public function hasParent() : bool
    {
        return $this->parent() !== null;
    }

    /**
     * Checks if `$parentId` creates a recursive chain of parents.
     */
    public function isRecursiveParent(?int $parentId) : bool
    {
        if (!$this->isPersisted() || is_null($parentId)) {
            return false;
        }

        return $this->getId() === $parentId
            || ($this->hasParent() && $this->parent()->isRecursiveParent($parentId));
    }

    abstract public function getId() : ?int;
    abstract public function isPersisted() : bool;
}
