<?php

namespace Plasticode\Config\Interfaces;

interface TagsConfigInterface
{
    function getTab(string $class) : ?string;
}
