<?php

namespace Plasticode\Parsing\Interfaces;

interface EntityLinkMapperInterface extends LinkMapperInterface, LinkRendererInterface
{
    public function entity() : string;
}
