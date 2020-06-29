<?php

namespace Plasticode\Core\Interfaces;

use Plasticode\Collections\TagLinkCollection;
use Plasticode\Models\Interfaces\PageInterface;
use Plasticode\Models\Interfaces\TaggedInterface;

interface LinkerInterface
{
    function abs(string $url = null) : string;
    function rel(string $url = null) : string;

    function getImageExtension(?string $type) : string;

    function page(PageInterface $page = null) : string;
    function news(int $id = null) : string;
    function tag(string $tag = null, string $tab = null) : string;

    function newsYear(int $year) : string;

    function twitch(string $id) : string;
    function twitchImg(string $id) : string;
    function twitchLargeImg(string $id) : string;

    function youtube(string $code) : string;

    function gravatarUrl(string $hash = null) : string;

    function tagLinks(TaggedInterface $entity) : TagLinkCollection;
}
