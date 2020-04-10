<?php

namespace Plasticode\Exceptions\Http;

class BadRequestException extends HttpException
{
    protected static $defaultMessage = 'Bad request.';
    protected static $errorCode = 400;
}
