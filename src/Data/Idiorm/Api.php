<?php

namespace Plasticode\Data\Idiorm;

use ORM;
use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Collections\Generic\Collection;
use Plasticode\Core\Response;
use Plasticode\Data\DbMetadata;
use Plasticode\Data\Interfaces\ApiInterface;
use Plasticode\Data\Rights;
use Plasticode\Exceptions\Http\NotFoundException;
use Plasticode\Exceptions\Http\AuthorizationException;
use Plasticode\Generators\Interfaces\EntityGeneratorInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Util\Date;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class Api implements ApiInterface
{
    private Access $access;
    private AuthInterface $auth;
    private DbMetadata $metadata;
    private LoggerInterface $logger;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        Access $access,
        AuthInterface $auth,
        DbMetadata $metadata,
        LoggerInterface $logger,
        UserRepositoryInterface $userRepository
    )
    {
        $this->access = $access;
        $this->auth = $auth;
        $this->metadata = $metadata;
        $this->logger = $logger;
        $this->userRepository = $userRepository;
    }

    /**
     * Returns access rights for the table.
     */
    private function getRights(string $table): Rights
    {
        return $this->access->getTableRights(
            $table,
            $this->auth->getUser()
        );
    }

    public function get(
        ResponseInterface $response,
        int $id,
        EntityGeneratorInterface $generator
    ): ResponseInterface
    {
        $table = $generator->getEntity();

        $obj = $this->getObj($table, $id);

        if (is_null($obj)) {
            throw new NotFoundException();
        }

        $rights = $this->getRights($table);
        $item = $obj->asArray();

        if (!$rights->canEntity($item, Rights::API_READ)) {
            $this->logger->info(
                'Unauthorized read attempt on ' . $table . ': ' . $item['id']
            );

            throw new AuthorizationException();
        }

        $item = $generator->afterLoad($item);
        $item = $this->addUserNames($table, $item);
        $item = $rights->enrichRights($item);

        return Response::json($response, $item);
    }

    public function getMany(
        ServerRequestInterface $request,
        ResponseInterface $response,
        EntityGeneratorInterface $generator,
        array $options = []
    ): ResponseInterface
    {
        $table = $generator->getEntity();
        $rights = $this->getRights($table);

        if (!$rights->can(Rights::API_READ)) {
            $this->logger->info(
                'Unauthorized read attempt on ' . $table
            );

            throw new AuthorizationException();
        }

        $exclude = $options['exclude'] ?? null;

        $items = $this->selectMany($table, $exclude);

        if (isset($options['filter'])) {
            $args = $options['args'];
            $items = $items->where($options['filter'], $args['id']);
        }

        $tableMetadata = $this->metadata->tableMetadata($table) ?? [];

        if (isset($tableMetadata['sort'])) {
            $sortBy = $tableMetadata['sort'];

            $items = isset($tableMetadata['reverse'])
                ? $items->orderByDesc($sortBy)
                : $items->orderByAsc($sortBy);
        }

        // populate array
        $array = $items->findArray();

        $items = Collection::make($array)
            ->where(
                fn ($item) => $rights->canEntity($item, Rights::READ)
            )
            ->map(
                function ($item) use ($generator, $table, $rights) {
                    $item = $generator->afterLoad($item);
                    $item = $this->addUserNames($table, $item);
                    $item = $rights->enrichRights($item);

                    return $item;
                }
            );

        return Response::json($response, $items, $options);
    }

    public function create(
        ServerRequestInterface $request,
        ResponseInterface $response,
        EntityGeneratorInterface $generator
    ): ResponseInterface
    {
        $table = $generator->getEntity();
        $rights = $this->getRights($table);

        if (!$rights->can(Rights::CREATE)) {
            $this->logger->info(
                'Unauthorized create attempt on ' . $table
            );

            throw new AuthorizationException();
        }

        $original = $request->getParsedBody();

        $data = $this->securePublished($table, $original);
        $data = $this->stamps($table, $data);

        $generator->validate($request, $data);

        $data = $generator->beforeSave($data);

        $entity = $this->forTable($table)->create($data);
        $entity->save();

        $this->logger->info('Created ' . $table . ': ' . $entity->id);

        $generator->afterSave($entity->asArray(), $original);

        return $this
            ->get($response, $entity->id, $generator)
            ->withStatus(201);
    }

    public function update(
        ServerRequestInterface $request,
        ResponseInterface $response,
        int $id,
        EntityGeneratorInterface $generator
    ): ResponseInterface
    {
        $table = $generator->getEntity();
        $rights = $this->getRights($table);

        $entity = $this->getObj($table, $id);

        if (!$entity) {
            throw new NotFoundException();
        }

        if (!$rights->canEntity($entity->asArray(), Rights::EDIT)) {
            $this->logger->info(
                'Unauthorized edit attempt on ' . $table . ': ' . $entity->id
            );

            throw new AuthorizationException();
        }

        $original = $request->getParsedBody();

        $data = $this->securePublished($table, $original);
        $data = $this->stamps($table, $data);

        $generator->validate($request, $data, $id);

        $data = $generator->beforeSave($data, $id);

        $entity->set($data);
        $entity->save();

        $this->logger->info(
            'Updated ' . $table . ': ' . $entity->id
        );

        $repository = $generator->getRepository();
        $repository->deleteCachedEntity($entity->id);

        $generator->afterSave($entity->asArray(), $original);

        return $this->get($response, $entity->id, $generator);
    }

    public function delete(
        ResponseInterface $response,
        int $id,
        EntityGeneratorInterface $generator
    ): ResponseInterface
    {
        $table = $generator->getEntity();
        $rights = $this->getRights($table);

        $entity = $this->getObj($table, $id);

        if (!$entity) {
            throw new NotFoundException();
        }

        if (!$rights->canEntity($entity->asArray(), Rights::DELETE)) {
            $this->logger->info(
                'Unauthorized delete attempt on ' . $table . ': ' . $entity->id
            );

            throw new AuthorizationException();
        }

        $entity->delete();

        $this->logger->info(
            'Deleted ' . $table . ': ' . $entity->id
        );

        $repository = $generator->getRepository();
        $repository->deleteCachedEntity($entity->id);

        $generator->afterDelete($entity->asArray());

        return $response->withStatus(204);
    }

    /**
     * Adds user names for created_by / updated_by.
     */
    private function addUserNames(string $table, array $item): array
    {
        if ($this->metadata->hasField($table, 'created_by')) {
            $creator = '[no data]';

            if (isset($item['created_by'])) {
                $created = $this->userRepository->get($item['created_by']);

                $creator = ($created !== null)
                    ? $created->displayName()
                    : $item['created_by'];
            }

            $item['created_by_name'] = $creator;
        }

        if ($this->metadata->hasField($table, 'updated_by')) {
            $updater = '[no data]';

            if (isset($item['updated_by'])) {
                $updated = $this->userRepository->get($item['updated_by']);

                $updater = ($updated !== null)
                    ? $updated->displayName()
                    : $item['updated_by'];
            }

            $item['updated_by_name'] = $updater;
        }

        return $item;
    }

    /**
     * Unsets 'published' property if the user has no rights to change it.
     * 
     * This is a security check,
     * alternatively ~NotAuthorized() exception can be thrown.
     */
    private function securePublished(string $table, array $data): array
    {
        $rights = $this->getRights($table);
        $canPublish = $rights->can(Rights::PUBLISH);

        if (isset($data['published']) && !$canPublish) {
            unset($data['published']);
        }

        return $data;
    }

    /**
     * Adds updated_at / created_by / updated_by values.
     */
    private function stamps(string $table, array $data): array
    {
        $upd = $this->metadata->hasField($table, 'updated_at')
            ? Date::dbNow()
            : null;

        if ($upd) {
            $data['updated_at'] = $upd;
        }

        $user = $this->auth->getUser();

        if ($user) {
            $userId = $user->getId();
            $createdBy = $data['created_by'] ?? null;

            if ($this->metadata->hasField($table, 'created_by') && is_null($createdBy)) {
                $data['created_by'] = $userId;
            }

            if ($this->metadata->hasField($table, 'updated_by')) {
                $data['updated_by'] = $userId;
            }
        }

        return $data;
    }

    private function selectMany(string $tableAlias, ?array $exclude = null): ORM
    {
        $table = $this->forTable($tableAlias);
        $fields = $this->metadata->fields($tableAlias);

        if (!empty($fields) && !empty($exclude)) {
            $fields = array_diff($fields, $exclude);
        }

        return !empty($fields)
            ? $table->selectMany($fields)
            : $table->selectMany();
    }

    private function getObj(string $tableAlias, $id, ?callable $where = null): ORM
    {
        $query = $this
            ->forTable($tableAlias)
            ->where('id', $id);

        if ($where) {
            $query = $where($query);
        }

        return $query->findOne();
    }

    private function forTable(string $tableAlias): ORM
    {
        $tableName = $this->metadata->tableName($tableAlias);

        return ORM::forTable($tableName);
    }

    public function getQueryCount(): int
    {
        return ORM::forTable(null)
            ->rawQuery('SHOW STATUS LIKE ?', ['Questions'])
            ->findOne()['Value'];
    }
}
