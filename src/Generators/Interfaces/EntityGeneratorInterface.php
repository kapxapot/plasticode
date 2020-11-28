<?php

namespace Plasticode\Generators\Interfaces;

use Slim\App;
use Slim\Http\Request;

interface EntityGeneratorInterface
{
    /**
     * Get entity name.
     */
    function getEntity() : string;

    function validate(Request $request, array $data, $id = null) : void;

    function afterLoad(array $item) : array;
    function beforeSave(array $data, $id = null) : array;
    function afterSave(array $item, array $data) : void;
    function afterDelete(array $item) : void;

    /**
     * Generates API routes based on settings.
     *
     * @param callable $access Creates {@see \Plasticode\Middleware\AccessMiddleware}.
     */
    function generateAPIRoutes(App $app, callable $access) : void;

    /**
     * Generates admin page route.
     *
     * @param callable $access Creates {@see \Plasticode\Middleware\AccessMiddleware}.
     */
    function generateAdminPageRoute(App $app, callable $access) : void;
}
