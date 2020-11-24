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
    function root() : self;

    /**
     * :(
     */
    function isOrphan() : bool;

    function hasParent() : bool;

    /**
     * Checks if the $parentId creates a recursive chain of parents.
     */
    function isRecursiveParent(?int $parentId) : bool;
}
