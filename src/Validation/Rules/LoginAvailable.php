<?php

namespace Plasticode\Validation\Rules;

use Plasticode\Repositories\Interfaces\Basic\FieldValidatingRepositoryInterface;

class LoginAvailable extends TableFieldAvailable
{
    public function __construct(
        FieldValidatingRepositoryInterface $repository,
        ?int $exceptId = null
    )
    {
        parent::__construct($repository, 'login', $exceptId);
    }
}
