<?php

namespace Plasticode\Interfaces;

/**
 * Something that relates to some entity.
 */
interface EntityRelatedInterface
{
    /**
     * Returns the related entity class name.
     */
    function getEntityClass(): string;
}
