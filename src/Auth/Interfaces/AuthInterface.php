<?php

namespace Plasticode\Auth\Interfaces;

use Plasticode\Models\AuthToken;
use Plasticode\Models\Role;
use Plasticode\Models\User;

interface AuthInterface
{
    /**
     * Set current auth token.
     */
    function setToken(AuthToken $token) : void;

    /**
     * Resets (deletes) current auth token.
     */
    public function resetToken() : void;

    public function getTokenId() : ?int;

    /**
     * Get current auth token.
     */
    public function getToken() : ?AuthToken;

    /**
     * Get current user.
     */
    public function getUser() : ?User;

    /**
     * Get current role.
     */
    public function getRole() : ?Role;
}
