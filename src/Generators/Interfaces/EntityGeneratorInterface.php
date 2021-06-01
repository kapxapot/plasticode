<?php

namespace Plasticode\Generators\Interfaces;

use Plasticode\Interfaces\EntityRelatedInterface;
use Plasticode\Repositories\Interfaces\Generic\RepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

interface EntityGeneratorInterface extends EntityRelatedInterface
{
    /**
     * Get entity name (plural form in snake case like 'auth_tokens').
     */
    public function getEntity(): string;

    public function getRepository(): RepositoryInterface;

    public function validate(ServerRequestInterface $request, array $data, $id = null): void;

    public function afterLoad(array $item): array;

    public function beforeSave(array $data, $id = null): array;

    public function afterSave(array $item, array $data): void;

    public function afterDelete(array $item): void;

    /**
     * Generates API routes based on settings.
     */
    public function generateAPIRoutes(App $app): void;

    /**
     * Generates admin page route.
     */
    public function generateAdminPageRoute(App $app): void;
}
