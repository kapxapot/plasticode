<?php

namespace Plasticode\Models\Interfaces;

interface UpdatedAtInterface extends DbModelInterface
{
    public function updatedAtIso(): string;
}
