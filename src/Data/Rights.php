<?php

namespace Plasticode\Data;

use Plasticode\Models\User;

/**
 * Table rights that can be checked for
 * the current user & exact entities (table records).
 */
class Rights
{
    const API_READ = 'api_read';
    const API_CREATE = 'api_create';
    const API_EDIT = 'api_edit';
    const API_DELETE = 'api_delete';

    const READ = 'read';
    const READ_OWN = 'read_own';
    const CREATE = 'create';
    const EDIT = 'edit';
    const EDIT_OWN = 'edit_own';
    const DELETE = 'delete';
    const DELETE_OWN = 'delete_own';
    const PUBLISH = 'publish';

    private ?User $user;

    /**
     * Table access rights
     * 
     * @var array<string, boolean>
     */
    private array $rights;

    /**
     * @param array<string, boolean> $rights
     */
    public function __construct(?User $user, array $rights)
    {
        $this->user = $user;
        $this->rights = $rights;
    }

    /**
     * Get access rights for table.
     * 
     * @return array<string, boolean>
     */
    public function forTable() : array
    {
        return $this->rights;
    }

    /**
     * Get access rights for entity.
     * 
     * @return array<string, boolean>
     */
    public function forEntity(array $entity) : array
    {
        $createdBy = $entity['created_by'] ?? null;

        $noOwner = is_null($createdBy);

        $own = !is_null($this->user)
            && $createdBy == $this->user->getId();

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
     * Adds edit/delete rights to entity.
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
     */
    public function can(string $rights) : bool
    {
        return $this->forTable()[$rights] ?? false;
    }

    /**
     * Has entity rights?
     */
    public function canEntity(array $entity, string $rights) : bool
    {
        return $this->forEntity($entity)[$rights] ?? false;
    }
}
