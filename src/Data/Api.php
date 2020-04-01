<?php

namespace Plasticode\Data;

use Plasticode\Auth\Access;
use Plasticode\Auth\Auth;
use Plasticode\Collection;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\NotFoundException;
use Plasticode\Exceptions\Http\AuthorizationException;
use Plasticode\Generators\EntityGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class Api
{
    private Access $access;
    private Auth $auth;
    private Db $db;
    private LoggerInterface $logger;

    public function __construct(
        Access $access,
        Auth $auth,
        Db $db,
        LoggerInterface $logger
    )
    {
        $this->access = $access;
        $this->auth = $auth;
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Returns access rights for the table.
     */
    private function getRights(string $table) : Rights
    {
        return $this->access->getEntityRights(
            $table,
            $this->auth->getUser()
        );
    }

    /**
     * Returns entity by id.
     *
     * @param ResponseInterface $response
     * @param mixed $id
     * @param EntityGenerator $provider
     * @return ResponseInterface
     */
    public function get(
        ResponseInterface $response,
        $id,
        EntityGenerator $provider
    ) : ResponseInterface
    {
        $table = $provider->getEntity();

        $obj = $this->db->getObj($table, $id);

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

        $item = $provider->afterLoad($item);
        $item = $this->db->addUserNames($table, $item);
        $item = $rights->enrichRights($item);

        return Response::json($response, $item);
    }

    /**
     * Returns many entities.
     *
     * @param ResponseInterface $response
     * @param EntityGenerator $provider
     * @param array $options
     * @return ResponseInterface
     */
    public function getMany(
        ResponseInterface $response,
        EntityGenerator $provider,
        array $options = []
    ) : ResponseInterface
    {
        $table = $provider->getEntity();
        $rights = $this->getRights($table);

        if (!$rights->can(Rights::API_READ)) {
            $this->logger->info(
                'Unauthorized read attempt on ' . $table
            );

            throw new AuthorizationException();
        }
        
        $exclude = $options['exclude'] ?? null;

        $items = $this->db->selectMany($table, $exclude);

        if (isset($options['filter'])) {
            $items = $this->db->filterBy(
                $items, $options['filter'], $options['args']
            );
        }

        $settings = $this->db->getTableSettings($table) ?? [];
        
        if (isset($settings['sort'])) {
            $sortBy = $settings['sort'];

            $items = isset($settings['reverse'])
                ? $items->orderByDesc($sortBy)
                : $items->orderByAsc($sortBy);
        }

        // populate array
        $array = $items->findArray();

        $items = Collection::make($array)
            ->where(
                function ($item) use ($rights) {
                    return $rights->canEntity($item, Rights::READ);
                }
            )
            ->map(
                function ($item) use ($provider, $table, $rights) {
                    $item = $provider->afterLoad($item);
                    $item = $this->db->addUserNames($table, $item);
                    $item = $rights->enrichRights($item);

                    return $item;
                }
            );

        return Response::json($response, $items, $options);
    }
    
    /**
     * Creates entity.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param EntityGenerator $provider
     * @return ResponseInterface
     */
    public function create(
        ServerRequestInterface $request,
        ResponseInterface $response,
        EntityGenerator $provider
    ) : ResponseInterface
    {
        $table = $provider->getEntity();
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
        
        $provider->validate($request, $data);
        
        $data = $provider->beforeSave($data);

        $entity = $this->db->create($table, $data);
        $entity->save();
        
        $provider->afterSave($entity->asArray(), $original);

        $this->logger->info('Created ' . $table . ': ' . $entity->id);
        
        return $this->get($response, $entity->id, $provider)
            ->withStatus(201);
    }
    
    /**
     * Updates entity.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $id
     * @param EntityGenerator $provider
     * @return ResponseInterface
     */
    public function update(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $id,
        EntityGenerator $provider
    ) : ResponseInterface
    {
        $table = $provider->getEntity();
        $rights = $this->getRights($table);

        $entity = $this->db->getObj($table, $id);

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

        $provider->validate($request, $data, $id);
        
        $data = $provider->beforeSave($data, $id);

        $entity->set($data);
        $entity->save();
        
        $provider->afterSave($entity->asArray(), $original);
        
        $this->logger->info(
            'Updated ' . $table . ': ' . $entity->id
        );
        
        return $this->get($response, $entity->id, $provider);
    }
    
    /**
     * Deletes entity.
     *
     * @param ResponseInterface $response
     * @param mixed $id
     * @param EntityGenerator $provider
     * @return ResponseInterface
     */
    public function delete(
        ResponseInterface $response,
        $id,
        EntityGenerator $provider
    ) : ResponseInterface
    {
        $table = $provider->getEntity();
        $rights = $this->getRights($table);

        $entity = $this->db->getObj($table, $id);
        
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
        
        $provider->afterDelete($entity->asArray());

        $this->logger->info(
            'Deleted ' . $table . ': ' . $entity->id
        );
        
        return $response->withStatus(204);
    }

    /**
     * Unsets 'published' property if the user has no rights to change it.
     * 
     * This is a security check,
     * alternatively ~NotAuthorized() exception can be thrown.
     *
     * @param string $table
     * @param array $data
     * @return array
     */
    private function securePublished(string $table, array $data) : array
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
     *
     * @param string $table
     * @param array $data
     * @return array
     */
    private function stamps(string $table, array $data) : array
    {
        $upd = $this->db->updatedAt($table);

        if ($upd) {
            $data['updated_at'] = $upd;
        }
        
        $user = $this->auth->getUser();

        if ($user) {
            $data = $this->db->stampBy($table, $data, $user->getId());
        }

        return $data;
    }
}
