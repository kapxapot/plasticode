<?php

namespace Plasticode\External;

use Codebird\Codebird;
use Plasticode\Util\Strings;

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
    
    public function buildMessage(string $text, string $url = null, array $tags = null, string $embedUrl = null) : string
    {
        $chunks = [];
        
        $chunks[] = strip_tags($text);
        
        if (strlen($url) > 0) {
            $chunks[] = $url;
        }
        
        if (!empty($tags)) {
            $chunks[] = Strings::hashTags($tags);
        }

        if (strlen($embedUrl) > 0) {
            $chunks[] = $embedUrl;
        }
        
        return implode(' ', $chunks);
    }
}
