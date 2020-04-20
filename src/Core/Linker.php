<?php

namespace Plasticode\Core;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\IO\Image;
use Plasticode\Util\Numbers;
use Plasticode\Util\Strings;
use Slim\Interfaces\RouterInterface;

class Linker implements LinkerInterface
{
    protected SettingsProviderInterface $settingsProvider;
    protected RouterInterface $router;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        RouterInterface $router
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->router = $router;
    }

    /**
     * Makes url absolute. If no url provided, returns site url with trailing '/'.
     */
    public function abs(string $url = null) : string
    {
        $siteUrl = $this->settingsProvider
            ->get('view_globals.site_url');

        $baseUrl = rtrim($siteUrl, '/');

        if (strpos($url, $baseUrl) !== 0) {
            $url = $baseUrl . '/' . ltrim($url, '/');
        }

        return $url;
    }

    public function rel(string $url = null) : string
    {
        return $this->root() . rtrim($url, '/');
    }

    public function root() : string
    {
        return $this->settingsProvider->get('root');
    }

    /**
     * @deprecated 0.6.1
     */
    public function getExtension(?string $type) : ?string
    {
        return Image::getExtension($type ?? 'jpeg');
    }

    public function getImageExtension(?string $type) : string
    {
        return Image::getExtension($type) ?? 'jpg';
    }

    public function page(string $slug = null) : string
    {
        return $this->router->pathFor('main.page', ['slug' => $slug]);
    }

    public function news(int $id = null) : string
    {
        return $this->router->pathFor('main.news', ['id' => $id]);
    }

    public function tag(string $tag = null, string $tab = null) : string
    {
        $tag = Strings::fromSpaces($tag, '+');
        $url = $this->router->pathFor('main.tag', ['tag' => $tag]);
        
        if ($tab) {
            $url .= '#/' . $tab;
        }
        
        return $url;
    }
    
    public function twitchImg(string $id) : string
    {
        return 'https://static-cdn.jtvnw.net/previews-ttv/live_user_' . $id . '-320x180.jpg';
    }

    public function twitchLargeImg(string $id) : string
    {
        return 'https://static-cdn.jtvnw.net/previews-ttv/live_user_' . $id . '-640x360.jpg';
    }

    public function twitch(string $id) : string
    {
        return 'https://twitch.tv/' . $id;
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

    public function defaultGravatarUrl() : string
    {
        return $this->gravatarUrl();
    }

    public function randPic(int $width, int $height) : string
    {
        return 'https://picsum.photos/' . $width . '/' . $height . '?' . Numbers::generate(6);
    }
}
