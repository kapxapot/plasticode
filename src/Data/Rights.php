<?php

namespace Plasticode\Data;

use Plasticode\Auth\Access;

class Rights
{
    const API_READ = 'api_read';

    const READ = 'read';
    const READ_OWN = 'read_own';
    const CREATE = 'create';
    const EDIT = 'edit';
    const EDIT_OWN = 'edit_own';
    const DELETE = 'delete';
    const DELETE_OWN = 'delete_own';
    const PUBLISH = 'publish';

    /**
     * User
     *
     * @var Plasticode\Models\User
     */
    private $user;

    /**
     * Table access rights
     *
     * @var array
     */
    private $rights;

    public function __construct(Access $access, string $table)
    {
        $this->user = $access->getUser();
        $this->rights = $access->getAllRights($table);
    }

    /**
     * Get access rights for table
     *
     * @return array
     */
    public function forTable() : array
    {
        return $this->rights;
    }
    
    /**
     * Get access rights for entity
     *
     * @param array $entity
     * @return array
     */
    public function forEntity(array $entity) : array
    {
        $createdBy = $entity['created_by'] ?? null;

        $noOwner = is_null($createdBy);
        $own = !is_null($this->user) && $createdBy == $this->user->getId();

        $can = $this->forTable();

        $can[self::READ] = $noOwner
            || $can[self::READ]
            || ($own && $can[self::READ_OWN]);
        
        $can[self::EDIT] = $can[self::EDIT]
            || ($own && $can[self::EDIT_OWN]);
        
        $can[self::DELETE] = $can[self::DELETE]
            || ($own && $can[self::DELETE_OWN]);

        return $can;
    }

    /**
     * Adds edit/delete rights to entity
     *
     * @param array $entity
     * @return array
     */
    public function enrichRights(array $entity) : array
    {
        $rights = $this->forEntity($entity);

        $entity['access'] = [
            self::EDIT => $rights[self::EDIT],
            self::DELETE => $rights[self::DELETE],
        ];

        return $entity;
    }

    /**
     * Has table rights?
     *
     * @param string $rights
     * @return boolean
     */
    public function can(string $rights) : bool
    {
        return $this->forTable()[$rights] ?? false;
    }

    /**
     * Has entity rights?
     *
     * @param array $entity
     * @param string $rights
     * @return boolean
     */
    public function canEntity(array $entity, string $rights) : bool
    {
        return $this->forEntity($entity)[$rights] ?? false;
    }
}
