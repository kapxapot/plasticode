<?php

namespace Plasticode\Models\Interfaces;

use Plasticode\Collection;

interface SearchableInterface
{
    public static function search($query) : Collection;
    public function code() : string;
}
