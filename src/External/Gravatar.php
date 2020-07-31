<?php

namespace Plasticode\External;

class Gravatar
{
    public function hash(?string $email) : ?string
    {
        if (strlen($email) == 0) {
            return null;
        }

        $email = strtolower(trim($email));
        $hash = md5($email);

        return $hash;
    }
}
