<?php

namespace Plasticode\Testing\Mocks;

use Plasticode\Collections\TagLinkCollection;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\IO\Image;
use Plasticode\Models\Interfaces\TaggedInterface;
use Plasticode\TagLink;

class LinkerMock implements LinkerInterface
{
    public function abs(string $url = null) : string
    {
        return 'http://abs' . $url;
    }

    public function rel(string $url = null) : string
    {
        return '/' . rtrim($url, '/');
    }

    /**
     * @deprecated 0.6.1
     */
    public function getExtension(?string $type) : ?string
    {
        throw new \Exception('Do not you this method.');
    }

    public function getImageExtension(?string $type) : string
    {
        return Image::getExtension($type) ?? 'jpg';
    }

    public function page(string $slug = null) : string
    {
        return $this->abs('/') . $slug;
    }

    public function news(int $id = null) : string
    {
        return $this->abs('/news/') . $id;
    }

    public function tag(string $tag = null, string $tab = null) : string
    {
        return $this->abs('/tags/') . $tag;
    }

    public function twitch(string $id) : string
    {
        return 'https://twitch.tv/' . $id;
    }
    public function twitchImg(string $id) : string
    {
        return 'https://static-cdn.jtvnw.net/previews-ttv/live_user_' . $id . '-320x180.jpg';
    }

    public function twitchLargeImg(string $id) : string
    {
        return 'https://static-cdn.jtvnw.net/previews-ttv/live_user_' . $id . '-640x360.jpg';
    }

    public function youtube(string $code) : string
    {
        return 'https://youtube.com/watch?v=' . $code;
    }

    public function gravatarUrl(string $hash = null) : string
    {
        $hash = $hash ?? '0';

        return 'https://www.gravatar.com/avatar/' . $hash . '?s=100&d=mp';
    }

    public function tagLinks(
        TaggedInterface $entity,
        ?string $tab = null
    ) : TagLinkCollection
    {
        $tags = $entity->getTags();

        $tagLinks = array_map(
            fn (string $t) => new TagLink($t, $this->tag($t, $tab)),
            $tags
        );

        return TagLinkCollection::make($tagLinks);
    }
}
