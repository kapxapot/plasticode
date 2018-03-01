<?php

namespace Plasticode\External;

use Plasticode\Contained;

class Telegram extends Contained {
	public function sendMessage($channelId, $message) {
		$tgs = $this->getSettings('telegram');
		
		$botToken = $tgs['bot_token'];
		$channel = $tgs['channels'][$channelId];

		$this->curlSendMessage($botToken, $channel, $message);
	}

	private function curlSendMessage($botToken, $chatId, $message) {
		$url = "https://api.telegram.org/bot{$botToken}/sendMessage";
		$params = [
		    'chat_id' => '@' . $chatId,
		    'text' => $message,
		    'parse_mode' => 'html',
		];
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$result = curl_exec($ch);
		curl_close($ch);
		
		return $result;
	}
}
