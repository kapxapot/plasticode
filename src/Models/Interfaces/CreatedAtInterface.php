<?php

namespace Plasticode\Models\Interfaces;

/**
 * {@see DbModelInterface} wrapper with $createdAt property.
 * 
 * @property string|null $createdAt
 */
interface CreatedAtInterface extends DbModelInterface
{
    function createdAtIso() : string;

    /**
     * @param string|DateTime $date
     */
    function isNewerThan($date) : bool;
}
