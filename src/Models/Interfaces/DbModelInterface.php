<?php

namespace Plasticode\Models\Interfaces;

use Plasticode\Interfaces\ArrayableInterface;

interface DbModelInterface extends ArrayableInterface, EquatableInterface, SerializableInterface
{
    /**
     * Returns the id of the model.
     *
     * - Use getId() instead of id when $idField is custom.
     * - It is recommended to use getId() always for safer code.
     */
    public function getId(): ?int;

    public static function pluralAlias(): string;

    /**
     * Was model saved or not.
     */
    public function isPersisted(): bool;
}
