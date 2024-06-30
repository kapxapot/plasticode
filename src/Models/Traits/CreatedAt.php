<?php

namespace Plasticode\Models\Traits;

use Plasticode\Util\Date;

/**
 * Implements {@see \Plasticode\Models\Interfaces\CreatedAtInterface}.
 *
 * @property string|null $createdAt
 */
trait CreatedAt
{
    public function createdAtIso(): string
    {
        return Date::iso($this->createdAt);
    }

    /**
     * @param string|DateTime $date
     */
    public function isNewerThan($date) : bool
    {
        return $this->createdAt && Date::dt($this->createdAt) > Date::dt($date);
    }
}
