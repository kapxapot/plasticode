<?php

namespace Plasticode\Exceptions\Http;

class BadRequestException extends Exception
{
    protected static $defaultMessage = 'Bad request.';
    protected static $errorCode = 400;
}
