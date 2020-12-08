<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Models\Basic\DbModel;

/**
 * @method ModelDummy dummy()
 * @method static withDummy(ModelDummy|callable $dummy)
 */
class DbModelDummy extends DbModel
{
    protected function requiredWiths() : array
    {
        return ['dummy'];
    }
}
