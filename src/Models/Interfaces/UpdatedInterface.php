<?php

namespace Plasticode\Models\Interfaces;

use Plasticode\Models\User;

interface UpdatedInterface
{
    function updatedBy() : ?int;
    function withUpdater(User $updater) : self;
}
