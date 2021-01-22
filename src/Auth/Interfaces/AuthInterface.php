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
    function setToken(AuthToken $token): void;

    /**
     * Resets (deletes) the current auth token.
     */
    function resetToken(): void;

    /**
     * Returns the current auth token's id.
     */
    function getTokenId(): ?int;

    /**
     * Returns the current auth token.
     */
    function getToken(): ?AuthToken;

    /**
     * Returns the current user's id.
     */
    function getUserId(): ?int;

    /**
     * Returns the current user.
     */
    function getUser(): ?User;

    /**
     * Returns the current role's id.
     */
    function getRoleId(): ?int;

    /**
     * Returns the current role.
     */
    function getRole(): ?Role;
}
