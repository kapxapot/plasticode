<?php

namespace Plasticode\Models\Interfaces;

/**
 * @method static|null parent()
 * @method static withParent(static|callable|null $parent)
 */
interface ParentedInterface extends DbModelInterface
{
    /**
     * The ultimate parent.
     * 
     * @return static
     */
    public function root() : self;

    /**
     * :(
     */
    public function isOrphan() : bool;

    public function hasParent() : bool;

    /**
     * Checks if the $parentId creates a recursive chain of parents.
     */
    public function isRecursiveParent(?int $parentId) : bool;
}
