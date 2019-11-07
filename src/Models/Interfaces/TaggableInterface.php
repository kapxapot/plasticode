<?php

namespace Plasticode\Models\Interfaces;

interface TaggableInterface
{
    public function getTags() : array;
    public function tagLinks() : array;
}
