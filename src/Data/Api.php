<?php

namespace Plasticode\Data;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Plasticode\Contained;
use Plasticode\Core\Response;
use Plasticode\Exceptions\NotFoundException;
use Plasticode\Exceptions\AuthorizationException;

class Api extends Contained
{
    /**
     * Get entity
     *
     * @param ResponseInterface $response
     * @param string $table
     * @param mixed $id
     * @param object $provider
     * @return ResponseInterface
     */
    public function get(ResponseInterface $response, string $table, $id, object $provider) : ResponseInterface
    {
        $e = $this->db->selectMany($table)->findOne($id);

        if (!$e) {
            throw new NotFoundException();
        }

        if (!$this->db->can($table, 'api_read', $e)) {
            $this->logger->info("Unauthorized read attempt on {$table}: {$e->id}");

            throw new AuthorizationException();
        }
        
        $e = $provider->afterLoad($e);

        return Response::json($response, $e);
    }

    /**
     * Get many entities
     *
     * @param ResponseInterface $response
     * @param string $table
     * @param object $provider
     * @param array $options
     * @return ResponseInterface
     */
    public function getMany(ResponseInterface $response, string $table, object $provider, array $options = []) : ResponseInterface
    {
        if (!$this->db->can($table, 'api_read')) {
            $this->logger->info("Unauthorized read attempt on {$table}");

            throw new AuthorizationException();
        }
        
        $exclude = $options['exclude'] ?? null;

        $items = $this->db->selectMany($table, $exclude);

        if (isset($options['filter'])) {
            $items = $this->db->filterBy($items, $options['filter'], $options['args']);
        }

        $settings = $this->db->getTableSettings($table) ?? [];
        
        if (isset($settings['sort'])) {
            $sortBy = $settings['sort'];

            $items = isset($settings['reverse'])
                ? $items->orderByDesc($sortBy)
                : $items->orderByAsc($sortBy);
        }
        
        $array = $items->findArray();

        $tableRights = $this->db->getTableRights($table);

        $array = array_filter($array, array($tableRights, 'canRead'));
        $array = array_map(array($provider, 'afterLoad'), $array);
        $array = array_map(array($this, 'addUserNames'), $array);
        $array = array_map(array($tableRights, 'enrichRights'), $array);

        $items = array_values($array);

        return Response::json($response, $items, $options);
    }
        
    private function addUserNames($item)
    {
        if (isset($item['created_by'])) {
            $created = $this->userRepository->get($item['created_by']);
            $item['created_by_name'] = $created->login ?? $item['created_by'];
        }

        if (isset($item['updated_by'])) {
            $updated = $this->userRepository->get($item['updated_by']);
            $item['updated_by_name'] = $updated->login ?? $item['updated_by'];
        }
        
        return $item;
    }
    
    /**
     * Create entity
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $table
     * @param object $provider
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request, ResponseInterface $response, string $table, object $provider) : ResponseInterface
    {
        if (!$this->db->can($table, 'create')) {
            $this->logger->info("Unauthorized create attempt on {$table}");

            throw new AuthorizationException();
        }

        $original = $request->getParsedBody();
        $data = $this->beforeValidate($table, $original);
        
        $provider->validate($request, $data);
        
        $data = $provider->beforeSave($data);

        $e = $this->db->create($table, $data);
        $e->save();
        
        $provider->afterSave($e, $original);

        $this->logger->info("Created {$table}: {$e->id}");
        
        return $this->apiGet($response, $table, $e->id, $provider)
            ->withStatus(201);
    }
    
    /**
     * Update entity
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $table
     * @param mixed $id
     * @param object $provider
     * @return ResponseInterface
     */
    public function update(ServerRequestInterface $request, ResponseInterface $response, string $table, $id, object $provider) : ResponseInterface
    {
        $e = $this->db->forTable($table)->findOne($id);

        if (!$e) {
            throw new NotFoundException();
        }

        if (!$this->db->can($table, 'edit', $e)) {
            $this->logger->info("Unauthorized edit attempt on {$table}: {$e->id}");

            throw new AuthorizationException();
        }

        $original = $request->getParsedBody();
        $data = $this->beforeValidate($table, $original, $id);

        $provider->validate($request, $data, $id);
        
        $data = $provider->beforeSave($data, $id);

        $e->set($data);
        $e->save();
        
        $provider->afterSave($e, $original);
        
        $this->logger->info("Updated {$table}: {$e->id}");
        
        return $this->get($response, $table, $e->id, $provider);
    }
    
    /**
     * Delete entity
     *
     * @param ResponseInterface $response
     * @param string $table
     * @param mixed $id
     * @param object $provider
     * @return ResponseInterface
     */
    public function delete(ResponseInterface $response, string $table, $id, object $provider) : ResponseInterface
    {
        $e = $this->db->forTable($table)->findOne($id);
        
        if (!$e) {
            throw new NotFoundException();
        }

        if (!$this->db->can($table, 'delete', $e)) {
            $this->logger->info("Unauthorized delete attempt on {$table}: {$e->id}");

            throw new AuthorizationException();
        }

        $e->delete();
        
        $provider->afterDelete($e);

        $this->logger->info("Deleted {$table}: {$e->id}");
        
        return $response->withStatus(204);
    }
}
