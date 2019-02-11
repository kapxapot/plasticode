<?php

namespace Plasticode\Controllers;

use Plasticode\Contained;
use Plasticode\Exceptions\NotFoundException;

class RestController extends Contained
{
	protected function notFound($message = null)
	{
	    throw new NotFoundException($message);
	}
}
