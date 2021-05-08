<?php

namespace Plasticode\Models\Traits;

/**
 * Implements {@see Plasticode\Models\Interfaces\GenderedInterface}.
 */
trait Gendered
{
    public function hasGender(): bool
    {
        return $this->gender() !== null;
    }

    abstract public function gender(): ?int;
}
