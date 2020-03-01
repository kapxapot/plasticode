<?php

namespace Plasticode\Parsing\Interfaces;

interface TaggedLinkMapperInterface extends LinkMapperInterface
{
    public function tag() : string;
}
