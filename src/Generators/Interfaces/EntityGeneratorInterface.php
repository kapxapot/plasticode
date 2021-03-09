<?php

namespace Plasticode\Generators\Interfaces;

use Plasticode\Interfaces\EntityRelatedInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

interface EntityGeneratorInterface extends EntityRelatedInterface
{
    /**
     * Get entity name (plural form in snake case like 'auth_tokens').
     */
    function getEntity(): string;

    function validate(ServerRequestInterface $request, array $data, $id = null): void;

    function afterLoad(array $item): array;
    function beforeSave(array $data, $id = null): array;
    function afterSave(array $item, array $data): void;
    function afterDelete(array $item): void;

    /**
     * Generates API routes based on settings.
     */
    function generateAPIRoutes(App $app): void;

    /**
     * Generates admin page route.
     */
    function generateAdminPageRoute(App $app): void;
}
