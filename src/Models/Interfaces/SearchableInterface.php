<?php

namespace Plasticode\Models\Interfaces;

use Plasticode\Collection;

interface SearchableInterface
{
    public static function search(string $searchQuery) : Collection;
    public function code() : string;
}
