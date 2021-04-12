<?php

namespace Plasticode\Models\Interfaces;

use Plasticode\Models\User;

interface CreatedInterface extends CreatedAtInterface
{
    public function isCreatedBy(User $user): bool;
}
