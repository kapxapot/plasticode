<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;
use Plasticode\Models\Traits\Updated;

class StampsDummy extends DbModel
{
    use Created;
    use Updated;

    protected function requiredWiths() : array
    {
        return ['creator', 'updater'];
    }
}
