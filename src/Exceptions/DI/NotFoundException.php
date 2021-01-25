<?php

namespace Plasticode\Exceptions\DI;

use Plasticode\Exceptions\Exception;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
