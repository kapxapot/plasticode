<?php

namespace Plasticode\Models\Interfaces;

interface CreatedAtInterface extends DbModelInterface
{
    public function createdAtIso(): string;

    /**
     * @param string|DateTime $date
     */
    public function isNewerThan($date): bool;
}
