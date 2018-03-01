<?php

namespace Plasticode\Core;

use Plasticode\Contained;
use Plasticode\Gallery\Gallery;

class Linker extends Contained {
	public function abs($url = null) {
		$baseUrl = rtrim($this->getSettings('view_globals.site_url'), '/');
		$url = ltrim($url, '/');
		
		return $baseUrl . '/' . $url;
	}

	public function getExtension($type) {
		return Gallery::getExtension($type ?? 'jpeg');		
	}

	/**
	 * For paging.
	 */
	function page($base, $page) {
		$delim = strpos($base, '?') !== false ? '&' : '?';
		return $base . ($page == 1 ? '' : "{$delim}page={$page}");
	}
	
	public function twitchImg($id) {
		return "//static-cdn.jtvnw.net/previews-ttv/live_user_{$id}-320x180.jpg";
	}
	
	public function twitchLargeImg($id) {
		return "//static-cdn.jtvnw.net/previews-ttv/live_user_{$id}-640x360.jpg";
	}
	
	public function twitch($id) {
		return 'http://twitch.tv/' . $id;
	}
}
