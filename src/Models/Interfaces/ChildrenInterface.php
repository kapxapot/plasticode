<?php

namespace Plasticode\Models\Interfaces;

use Plasticode\Collection;

interface ChildrenInterface
{
    /**
     * Get entity id.
     */
    function getId() : ?int;

    /**
     * Get entity parent id.
     * Null = no parent.
     */
    function parentId() : ?int;

    function withParent(?self $parent) : self;
    function withChildren(Collection $children) : self;
}
