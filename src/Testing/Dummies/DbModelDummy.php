<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Models\Generic\DbModel;

/**
 * @method ModelDummy dummy()
 * @method static withDummy(ModelDummy|callable $dummy)
 */
class DbModelDummy extends DbModel
{
    protected function requiredWiths(): array
    {
        return ['dummy'];
    }
}
