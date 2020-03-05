<?php

namespace Plasticode\Testing\Dummies;

class DummyModel
{
    /** @var integer */
    public $id;

    /** @var string */
    public $name;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}
