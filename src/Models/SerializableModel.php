<?php

namespace Plasticode\Models;

abstract class SerializableModel extends Model
{
    public abstract function serialize();
}
