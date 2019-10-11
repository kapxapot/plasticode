<?php

namespace Plasticode\External;

use Codebird\Codebird;
use Plasticode\Util\Strings;

class Twitter
{
    /**
     * Codebird
     *
     * @var Codebird\Codebird
     */
    private $cb;
    
    public function __construct(array $settings)
    {
        Codebird::setConsumerKey(
            $settings['consumer_key'],
            $settings['consumer_secret']
        );
    
        $this->cb = Codebird::getInstance();

        $this->cb->setToken(
            $settings['access_token'],
            $settings['access_key']
        );
    }
    
    public function tweet(string $message)
    {
        return $this->cb->statuses_update(
            ['status' => $message]
        );
    }
    
    public function buildMessage(string $text, string $url = null, array $tags = null) : string
    {
        $chunks = [];
        
        $chunks[] = strip_tags($text);
        
        if (strlen($url) > 0) {
            $chunks[] = $url;
        }
        
        if (!empty($tags)) {
            $chunks[] = Strings::hashTags($tags);
        }
        
        return implode(' ', $chunks);
    }
}
