<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Models\Basic\DbModel;
use Plasticode\Models\Traits\Stamps;

class StampsDummy extends DbModel
{
    use Stamps;

    protected function requiredWiths() : array
    {
        return [
            'creator',
            'updater',
        ];
    }
}
