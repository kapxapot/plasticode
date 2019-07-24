<?php

namespace Plasticode\Exceptions\Http;

use Plasticode\Exceptions\Exception;
use Plasticode\Exceptions\Interfaces\HttpExceptionInterface;
use Plasticode\Exceptions\Interfaces\PropagatedExceptionInterface;

abstract class HttpException extends Exception implements HttpExceptionInterface, PropagatedExceptionInterface
{
    /**
     * Default error message
     *
     * @var string
     */
    protected static $defaultMessage;

    /**
     * HTTP error code
     *
     * @var integer
     */
    protected static $errorCode = 500;

    /**
     * Created HttpException
     *
     * @param string $message Custom error message
     */
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? static::$defaultMessage);
    }
    
    /**
     * Returns HTTP error code
     *
     * @return integer
     */
    public function GetErrorCode() : int
    {
        return static::$errorCode;
    }
}
