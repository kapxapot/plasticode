<?php

namespace Plasticode\Models;

/**
 * @property integer $id
 * @property integer $userId
 * @property string $token
 * @property string|null $expiresAt
 * @property string $createdAt
 * @property string $updatedAt
 */
class AuthToken extends DbModel
{
    /**
     * Todo: move this to mapper
     *
     * @return User
     */
    public function user() : User
    {
        return self::$container->userRepository->get($this->userId);
    }

    public function toString() : string
    {
        return $this->token . ', expires at ' . $this->expiresAt;
    }
}
