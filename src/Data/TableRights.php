<?php

namespace Plasticode\Data;

use Plasticode\Auth\Access;

class TableRights
{
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
     * Get table access rights for user and item (optionally)
     *
     * @param array $item
     * @return array
     */
    public function get(array $item = null) : array
    {
        $can = $this->rights;

        if ($item) {
            $noOwner = !isset($item['created_by']);
            $own = !is_null($this->user) && $this->user->isOwnerOf($item);

            $can['read'] = $noOwner || $can['read'] || ($own && $can['read_own']);
            $can['edit'] = $can['edit'] || ($own && $can['edit_own']);
            $can['delete'] = $can['delete'] || ($own && $can['delete_own']);
        }

        return $can;
    }
    
    /**
     * Adds edit/delete rights to item
     *
     * @param array $item
     * @return array
     */
    public function enrichRights(array $item) : array
    {
        if ($item) {
            $rights = $this->get($item);
    
            if ($rights) {
                $item['access']['edit'] = $rights['edit'];
                $item['access']['delete'] = $rights['delete'];
            }
        }

        return $item;
    }
    
    /**
     * Get table read rights for user and item (optionally)
     *
     * @param array $item
     * @return boolean
     */
    public function canRead(array $item) : bool
    {
        $rights = $this->get($item);
        return $rights['read'];
    }
}
