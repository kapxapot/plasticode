<?php

namespace Plasticode\Auth;

use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Core\Interfaces\SessionInterface;
use Plasticode\Models\AuthToken;
use Plasticode\Models\Role;
use Plasticode\Models\User;

class Auth implements AuthInterface
{
    private SessionInterface $session;

    /**
     * The current auth token.
     */
    private ?AuthToken $token = null;

    public function __construct(
        SessionInterface $session
    )
    {
        $this->session = $session;
    }

    public function setToken(AuthToken $token): void
    {
        $this->token = $token;
        $this->session->set('token_id', $token->id);
    }

    public function resetToken(): void
    {
        $this->token = null;
        $this->session->delete('token_id');
    }

    public function getTokenId() : ?int
    {
        return $this->session->get('token_id');
    }

    public function getToken(): ?AuthToken
    {
        return $this->token;
    }

    public function getUserId(): ?int
    {
        return $this->getToken()
            ? $this->getToken()->userId
            : null;
    }

    public function getUser(): ?User
    {
        return $this->getToken()
            ? $this->getToken()->user()
            : null;
    }

    public function getRoleId(): ?int
    {
        return $this->getUser()
            ? $this->getUser()->roleId
            : null;
    }

    public function getRole(): ?Role
    {
        return $this->getUser()
            ? $this->getUser()->role()
            : null;
    }
}
