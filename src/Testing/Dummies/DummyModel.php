<?php

namespace Plasticode\Testing\Dummies;

class DummyModel
{
    public int $id;
    public string $name;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }
}
