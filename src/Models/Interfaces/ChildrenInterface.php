<?php

namespace Plasticode\Models\Interfaces;

use Plasticode\Collection;

interface ChildrenInterface
{
    /**
     * Get entity id.
     *
     * @return integer|string|null
     */
    function getId();

    /**
     * Get entity parent id.
     * Null = no parent.
     *
     * @return integer|string|null
     */
    function parentId();

    function withParent(?self $parent) : self;
    function withChildren(Collection $children) : self;
}
