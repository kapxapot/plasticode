<?php

namespace Plasticode\Core;

use Plasticode\Contained;
use Plasticode\IO\Image;
use Plasticode\Util\Strings;

class Linker extends Contained
{
    public function abs($url = null)
    {
        $baseUrl = rtrim($this->getSettings('view_globals.site_url'), '/');
        
        if (strpos($url, $baseUrl) !== 0) {
            $url = $baseUrl . '/' . ltrim($url, '/');
        }
        
        return $url;
    }
    
    public function root()
    {
        return $this->getSettings('root');
    }

    public function getExtension($type)
    {
        return Image::getExtension($type ?? 'jpeg');		
    }

    /**
     * For paging.
     */
    function page($base, $page)
    {
        $delim = strpos($base, '?') !== false ? '&' : '?';
        return $base . ($page == 1 ? '' : "{$delim}page={$page}");
    }

    public function tag($tag = null, $tab = null)
    {
        $tag = Strings::fromSpaces($tag, '+');
        $url = $this->router->pathFor('main.tag', [ 'tag' => $tag ]);
        
        if ($tab) {
            $url .= '#/' . $tab;
        }
        
        return $url;
    }

    // Twitch
    
    public function twitchImg($id)
    {
        return "//static-cdn.jtvnw.net/previews-ttv/live_user_{$id}-320x180.jpg";
    }
    
    public function twitchLargeImg($id)
    {
        return "//static-cdn.jtvnw.net/previews-ttv/live_user_{$id}-640x360.jpg";
    }
    
    public function twitch($id)
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
