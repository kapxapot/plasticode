<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Models\DbModel;

/**
 * @method DummyModel dummy()
 * @method static withDummy(DummyModel|callable $dummy)
 */
class DummyDbModel extends DbModel
{
    protected function requiredWiths(): array
    {
        return ['dummy'];
    }
}
