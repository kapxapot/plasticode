<?php

namespace Plasticode\External;

use Codebird\Codebird;

class Twitter
{
    /** @var \Codebird\Codebird */
    private $codebird;
    
    public function __construct(array $settings)
    {
        Codebird::setConsumerKey(
            $settings['consumer_key'],
            $settings['consumer_secret']
        );
    
        $this->codebird = Codebird::getInstance();

        $this->codebird->setToken(
            $settings['access_token'],
            $settings['access_key']
        );
    }
    
    public function tweet(string $message)
    {
        return $this->codebird->statuses_update(
            ['status' => $message]
        );
    }
}
