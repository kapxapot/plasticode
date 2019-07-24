<?php

namespace Plasticode\Exceptions\Http;

class AuthorizationException extends HttpException
{
    protected static $defaultMessage = 'Insufficient access rights.';
    protected static $errorCode = 403;
}
