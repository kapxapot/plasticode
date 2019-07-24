<?php

namespace Plasticode\Exceptions\Http;

class NotFoundException extends HttpException
{
    protected static $defaultMessage = 'Not found.';
    protected static $errorCode = 404;
}
