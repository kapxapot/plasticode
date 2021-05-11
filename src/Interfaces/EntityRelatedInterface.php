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
    public function getEntityClass(): string;
}
