<?php

namespace Plasticode\Validation\Rules;

use Plasticode\Core\Security;
use Respect\Validation\Rules\AbstractRule;

class MatchesPassword extends AbstractRule
{
    protected $password;
    
    public function __construct($password)
    {
        $this->password = $password;
    }
    
    public function validate($input)
    {
        return Security::verifyPassword($input, $this->password);
    }
}
