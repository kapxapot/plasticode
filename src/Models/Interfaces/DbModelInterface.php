<?php

namespace Plasticode\Models\Interfaces;

use Plasticode\Interfaces\ArrayableInterface;

interface DbModelInterface extends ArrayableInterface
{
    /**
     * Returns the id of the model.
     * 
     * Use getId() instead of id when $idField is custom.
     * It is recommended to use getId() always for safer code.
     */
    function getId() : ?int;

    static function pluralAlias() : string;

    /**
     * Checks if two objects are equal.
     * 
     * Equal means:
     *  - Same class.
     *  - Same id.
     */
    function equals(?DbModelInterface $model) : bool;

    /**
     * Was model saved or not.
     */
    function isPersisted() : bool;
}
