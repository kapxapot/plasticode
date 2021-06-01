<?php

namespace Plasticode\Testing\Mocks\Repositories\Generic;

use Plasticode\Repositories\Interfaces\Generic\RepositoryInterface;

abstract class RepositoryMock implements RepositoryInterface
{
    public function deleteCachedEntity(int $id): void
    {
        // do nothing
    }
}
