<?php

namespace Plasticode\External;

use Plasticode\Contained;

class Twitch extends Contained {
	public function getStreamData($id) {
		$clientId = $this->getSettings('twitch.client_id');

		$url = "https://api.twitch.tv/kraken/streams?channel={$id}";

		$data = $this->curlGet($url, $clientId);
		$json = json_decode($data, true);

		return $json;
	}

	private function curlGet($url, $clientId) {
		$ch = curl_init();

		$headers = [ "Client-ID: {$clientId}" ];
	
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$data = curl_exec($ch);
		curl_close($ch);
		
		return $data;
	}
}
