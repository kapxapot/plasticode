<?php

namespace Plasticode\Parsing\Interfaces;

interface MapperSourceInterface
{
    public function getMapper(string $tag) : MapperInterface;
}
