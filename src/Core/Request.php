<?php

namespace Plasticode\Core;

use Plasticode\Collections\Generic\StringCollection;
use Plasticode\Util\Strings;
use Psr\Http\Message\ServerRequestInterface;

class Request
{
    /**
     * Is it a JSON request?
     */
    public static function isJson(ServerRequestInterface $request): bool
    {
        return StringCollection::make(
            $request->getHeader('content-type')
        )->any(
            fn (string $t) => Strings::startsWith($t, 'application/json')
        );
    }

    /**
     * Does the request accept JSON?
     */
    public static function acceptsJson(ServerRequestInterface $request): bool
    {
        return StringCollection::make(
            $request->getHeader('accept')
        )->any(
            fn (string $t) => Strings::startsWith($t, 'application/json')
        );
    }

    /**
     * Checks if the request is for DataTables data.
     */
    public static function isDataTables(ServerRequestInterface $request): bool
    {
        $params = $request->getQueryParams();

        $format = $params['format'] ?? null;

        return ($format === 'dt');
    }
}
