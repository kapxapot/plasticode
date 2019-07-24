<?php

namespace Plasticode\Exceptions\Http;

class AuthenticationException extends HttpException
{
    protected static $defaultMessage = 'Access denied.';
    protected static $errorCode = 401;
}
