<?php

namespace Plasticode\External;

use Plasticode\Settings\Interfaces\SettingsProviderInterface;

/**
 * @deprecated
 */
class Telegram
{
    private SettingsProviderInterface $settingsProvider;

    public function __construct(
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->settingsProvider = $settingsProvider;
    }

    public function sendMessage(string $channelId, string $message): void
    {
        $botToken = $this->settingsProvider->get('telegram.bot_token');
        $channel = $this->settingsProvider->get('telegram.channels.' . $channelId);

        $this->curlSendMessage($botToken, $channel, $message);
    }

    private function curlSendMessage(
        string $botToken,
        string $chatId,
        string $message
    ): string
    {
        $url = 'https://api.telegram.org/bot' . $botToken . '/sendMessage';

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
