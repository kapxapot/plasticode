<?php

namespace Plasticode\Models\Interfaces;

use Plasticode\Models\User;

interface CreatedInterface
{
    function createdBy() : ?int;
    function withCreator(User $creator) : self;
}
