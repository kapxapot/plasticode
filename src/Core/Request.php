<?php

namespace Plasticode\Core;

use Psr\Http\Message\ServerRequestInterface;

class Request
{
    /**
     * Is it a JSON request?
     *
     * @param ServerRequestInterface $request
     * @return boolean
     */
    public static function isJson(ServerRequestInterface $request) : bool
    {
        $contentType = $request->getContentType();
        return (strpos($contentType, 'application/json') !== false);
    }
}
