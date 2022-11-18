<?php

namespace Plasticode\Auth\Interfaces;

use Plasticode\Models\AuthToken;
use Plasticode\Models\Role;
use Plasticode\Models\User;

interface AuthInterface
{
    /**
     * Sets the current auth token.
     */
    public function setToken(AuthToken $token): void;

    /**
     * Resets (deletes) the current auth token.
     */
    public function resetToken(): void;

    /**
     * Returns the current auth token's id.
     */
    public function getTokenId(): ?int;

    /**
     * Returns the current auth token.
     */
    public function getToken(): ?AuthToken;

    /**
     * Returns the current user's id.
     */
    public function getUserId(): ?int;

    /**
     * Returns the current user.
     */
    public function getUser(): ?User;

    /**
     * Returns the current role's id.
     */
    public function getRoleId(): ?int;

    /**
     * Returns the current role.
     */
    public function getRole(): ?Role;
}
