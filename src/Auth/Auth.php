<?php

namespace Plasticode\Auth;

use Plasticode\Core\Interfaces\SessionInterface;
use Plasticode\Models\AuthToken;
use Plasticode\Models\Role;
use Plasticode\Models\User;

class Auth
{
    private SessionInterface $session;

    /**
     * Current auth token
     */
    private ?AuthToken $token = null;

    public function __construct(
        SessionInterface $session
    )
    {
        $this->session = $session;
    }

    /**
     * Set current auth token.
     */
    public function setToken(AuthToken $token) : void
    {
        $this->token = $token;
        $this->session->set('token_id', $token->id);
    }

    /**
     * Resets (deletes) current auth token.
     */
    public function resetToken() : void
    {
        $this->token = null;
        $this->session->delete('token_id');
    }

    public function getTokenId() : int
    {
        return $this->session->get('token_id');
    }

    /**
     * Get current auth token.
     */
    public function getToken() : ?AuthToken
    {
        return $this->token;
    }

    /**
     * Get current user.
     */
    public function getUser() : ?User
    {
        return $this->getToken()
            ? $this->getToken()->user()
            : null;
    }

    /**
     * Get current role.
     */
    public function getRole() : ?Role
    {
        return $this->getUser()
            ? $this->getUser()->role()
            : null;
    }
}
