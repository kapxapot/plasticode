<?php

namespace Plasticode\External;

class Twitch
{
    private $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function getStreamData(string $id) : array
    {
        $clientId = $this->settings['client_id'];

        $url = 'https://api.twitch.tv/kraken/streams?channel=' . $id;

        $data = $this->curlGet($url, $clientId);
        $json = json_decode($data, true);

        return $json;
    }

    private function curlGet(string $url, string $clientId)
    {
        $ch = curl_init();

        $headers = ['Client-ID: ' . $clientId];
    
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $data = curl_exec($ch);
        curl_close($ch);
        
        return $data;
    }
}
