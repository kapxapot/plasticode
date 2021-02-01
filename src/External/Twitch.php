<?php

namespace Plasticode\External;

use Plasticode\Settings\Interfaces\SettingsProviderInterface;

class Twitch
{
    private const BASE_URL = 'https://api.twitch.tv/helix/';

    private SettingsProviderInterface $settingsProvider;

    public function __construct(
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->settingsProvider = $settingsProvider;
    }

    public function getStreamData(string $id): array
    {
        $url = self::BASE_URL . 'streams?user_login=' . $id;
        return $this->getData($url);
    }

    public function getGameData($id): array
    {
        $url = self::BASE_URL . 'games?id=' . $id;
        return $this->getData($url);
    }

    public function getUserData($id): array
    {
        $url = self::BASE_URL . 'users?id=' . $id;
        return $this->getData($url);
    }

    private function getData(string $url): array
    {
        $data = $this->curlGet($url);
        $json = json_decode($data, true);

        return $json;
    }

    private function curlGet(string $url): string
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

    private function getClientId(): string
    {
        return $this->settingsProvider->get('twitch.client_id');
    }
}
