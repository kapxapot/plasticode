<?php

namespace Plasticode\Tests\Models;

use PHPUnit\Framework\TestCase;
use Plasticode\Models\AuthToken;
use Plasticode\Models\User;
use Plasticode\Util\Date;

final class AuthTokenTest extends TestCase
{
    public function testProperties() : void
    {
        $userId = 1;

        /** @var User */
        $user = User::create(
            [
                'id' => $userId,
            ]
        );

        $tokenId = 13;
        $tokenStr = 'abbaababsbsbsbsbs';
        $tokenExpiresAt = Date::generateExpirationTime(5);

        /** @var AuthToken */
        $token = AuthToken::create(
            [
                'id' => $tokenId,
                'user_id' => $userId,
                'token' => $tokenStr,
                'expires_at' => $tokenExpiresAt,
            ]
        );
        
        $token = $token->withUser($user);

        $this->assertInstanceOf(AuthToken::class, $token);

        $this->assertEquals($tokenId, $token->id);
        $this->assertEquals($tokenId, $token->getId());
        $this->assertEquals($userId, $token->userId);
        $this->assertEquals($tokenStr, $token->token);
        $this->assertEquals($tokenExpiresAt, $token->expiresAt);

        $this->assertInstanceOf(User::class, $token->user());
        $this->assertEquals($userId, $token->user()->id);

        $this->assertFalse($token->isExpired());
        $this->assertEquals(
            $tokenStr . ', expires at ' . $tokenExpiresAt,
            (string)$token
        );
    }
}
