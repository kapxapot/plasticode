<?php

namespace Plasticode\Core;

use Psr\Http\Message\ServerRequestInterface;

class Request
{
    /**
     * Is it a JSON request?
     */
    public static function isJson(ServerRequestInterface $request) : bool
    {
        return in_array(
            'application/json',
            $request->getHeader('content-type')
        );
    }

    /**
     * Does the request accept JSON?
     */
    public static function acceptsJson(ServerRequestInterface $request) : bool
    {
        return in_array(
            'application/json',
            $request->getHeader('accept')
        );
    }
}
