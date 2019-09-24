<?php

namespace Plasticode\External;

class Twitch
{
    private $settings;

    private $baseUrl = 'https://api.twitch.tv/helix/';

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function getStreamData(string $id) : array
    {
        $url = $this->baseUrl . 'streams?user_login=' . $id;
        return $this->getData($url);
    }
    
    public function getGameData($id) : array
    {
        $url = $this->baseUrl . 'games?id' . $id;
        return $this->getData($url);
    }
    
    public function getUserData($id) : array
    {
        $url = $this->baseUrl . 'users?id=' . $id;
        return $this->getData($url);
    }

    private function getData(string $url) : array
    {
        $data = $this->curlGet($url);
        $json = json_decode($data, true);

        return $json;
    }

    private function curlGet(string $url)
    {
        $ch = curl_init();

        $headers = [
            'Client-ID: ' . $this->getClientId()
        ];
    
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

    private function getClientId() : string
    {
        return $this->settings['client_id'];
    }
}
