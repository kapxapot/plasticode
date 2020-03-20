<?php

namespace Plasticode\Core\Interfaces;

interface LinkerInterface
{
    public function abs(string $url = null) : string;
    public function rel(string $url = null) : string;

    public function getExtension(?string $type) : ?string;

    public function page(string $slug = null) : string;
    public function news(int $id = null) : string;
    public function tag(string $tag = null, string $tab = null) : string;

    public function twitch(string $id) : string;
    public function youtube(string $code) : string;

    public function gravatarUrl(string $hash = null) : string;
}
