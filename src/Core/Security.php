<?php

namespace Plasticode\Core;

class Security
{
    static public function verifyPassword(string $password, string $hashedPassword) : bool
    {
        return password_verify($password, $hashedPassword);
    }

    static public function encodePassword(string $password) : string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    static public function rehashPasswordNeeded(string $password)
    {
        return password_needs_rehash($password, PASSWORD_DEFAULT);
    }
    
    static public function generateToken(int $length = 16) : string
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}
