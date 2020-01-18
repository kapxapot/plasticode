<?php

namespace Plasticode\Tests;

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
