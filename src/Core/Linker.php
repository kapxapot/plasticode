<?php

namespace Plasticode\Core;

use Plasticode\Contained;
use Plasticode\IO\Image;
use Plasticode\Util\Strings;

class Linker extends Contained
{
    /**
     * Makes url absolute. If no url provided, returns site url
     *
     * @param string $url
     * @return string
     */
    public function abs(string $url = null) : string
    {
        $baseUrl = rtrim($this->getSettings('view_globals.site_url'), '/');
        
        if (strpos($url, $baseUrl) !== 0) {
            $url = $baseUrl . '/' . ltrim($url, '/');
        }
        
        return $url;
    }
    
    public function root() : string
    {
        return $this->getSettings('root');
    }

    public function getExtension($type) : ?string
    {
        return Image::getExtension($type ?? 'jpeg');
    }

    /**
     * For paging
     */
    function page(string $base, int $page) : string
    {
        $delim = strpos($base, '?') !== false ? '&' : '?';
        return $base . ($page == 1 ? '' : "{$delim}page={$page}");
    }

    public function tag(string $tag = null, string $tab = null) : string
    {
        $tag = Strings::fromSpaces($tag, '+');
        $url = $this->router->pathFor('main.tag', [ 'tag' => $tag ]);
        
        if ($tab) {
            $url .= '#/' . $tab;
        }
        
        return $url;
    }

    // Twitch
    
    public function twitchImg(string $id) : string
    {
        return "https://static-cdn.jtvnw.net/previews-ttv/live_user_{$id}-320x180.jpg";
    }
    
    public function twitchLargeImg(string $id) : string
    {
        return "https://static-cdn.jtvnw.net/previews-ttv/live_user_{$id}-640x360.jpg";
    }
    
    public function twitch(string $id) : string
    {
        return 'http://twitch.tv/' . $id;
    }

    // Gravatar

    public function gravatarUrl(string $hash = null) : string
    {
        $hash = $hash ?? '0';

        return 'https://www.gravatar.com/avatar/' . $hash . '?s=100&d=mp';
    }

    public function defaultGravatarUrl() : string
    {
        return $this->gravatarUrl();
    }
}
