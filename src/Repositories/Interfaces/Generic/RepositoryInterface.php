<?php

namespace Plasticode\Repositories\Interfaces\Generic;

/**
 * Generic repository interface.
 */
interface RepositoryInterface
{
    /**
     * Remove entity from repository cache.
     */
    public function deleteCachedEntity(int $id): void;
}
