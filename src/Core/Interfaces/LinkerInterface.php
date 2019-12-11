<?php

namespace Plasticode\Core\Interfaces;

interface LinkerInterface
{
    public function abs(string $url = null) : string;
    public function tag(string $tag = null, string $tab = null) : string;
    public function youtube(string $code) : string;
}