<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;
use Plasticode\Models\Traits\Updated;

class StampsDummy extends DbModel
{
    use Created, Updated;
}
