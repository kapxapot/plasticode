<?php

namespace Plasticode\External;

use Codebird\Codebird;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

class Twitter
{
    private Codebird $codebird;

    public function __construct(
        SettingsProviderInterface $settingsProvider
    )
    {
        Codebird::setConsumerKey(
            $settingsProvider->get('twitter.consumer_key'),
            $settingsProvider->get('twitter.consumer_secret')
        );

        $this->codebird = Codebird::getInstance();

        $this->codebird->setToken(
            $settingsProvider->get('twitter.access_token'),
            $settingsProvider->get('twitter.access_key')
        );
    }

    public function tweet(string $message)
    {
        return $this->codebird->statuses_update(
            ['status' => $message]
        );
    }
}
