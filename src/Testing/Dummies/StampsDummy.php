<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Models\Basic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\Stamps;

class StampsDummy extends DbModel implements CreatedAtInterface, UpdatedAtInterface
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
