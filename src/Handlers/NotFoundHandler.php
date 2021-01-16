<?php

namespace Plasticode\Handlers;

use Plasticode\Controllers\Controller;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Plasticode\Handlers\Traits\NotFound;

class NotFoundHandler extends Controller implements NotFoundHandlerInterface
{
    use NotFound;
}
