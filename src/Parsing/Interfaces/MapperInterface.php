<?php

namespace Plasticode\Parsing\Interfaces;

interface MapperInterface
{
    public function map(string $content, array $attrs) : array;
}
