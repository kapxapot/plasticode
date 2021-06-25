<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Models\Interfaces\EquatableInterface;

class ModelDummy implements EquatableInterface
{
    public int $id;
    public string $name;
    public ?string $type = null;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return $this
     */
    public function withType(?string $type) : self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * For ObjectProxy test, don't delete.
     */
    public function getName() : string
    {
        return $this->name;
    }

    public function equals(?EquatableInterface $obj): bool
    {
        return $obj !== null
            && $obj instanceof self
            && $this->id == $obj->id;
    }
}
