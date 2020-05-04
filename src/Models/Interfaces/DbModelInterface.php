<?php

namespace Plasticode\Models\Interfaces;

interface DbModelInterface
{
    /**
     * Returns the id of the model.
     * 
     * Use getId() instead of id when $idField is custom.
     * It is recommended to use getId() always for safer code.
     */
    function getId() : ?int;
}
