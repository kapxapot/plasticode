<?php

namespace Plasticode\Core\Interfaces;

use Plasticode\Collections\TagLinkCollection;
use Plasticode\Models\Interfaces\PageInterface;
use Plasticode\Models\Interfaces\TaggedInterface;

interface LinkerInterface
{
    public function abs(string $url = null): string;
    public function rel(string $url = null): string;

    public function getImageExtension(?string $type): string;

    public function page(PageInterface $page = null): string;
    public function news(int $id = null): string;
    public function tag(string $tag = null, string $tab = null): string;

    public function newsYear(int $year): string;

    public function twitch(string $id): string;
    public function twitchImg(string $id): string;
    public function twitchLargeImg(string $id): string;

    public function youtube(string $code): string;

    public function defaultGravatarUrl(): string;
    public function gravatarUrl(string $hash = null): string;

    public function tagLinks(TaggedInterface $entity): TagLinkCollection;
}
