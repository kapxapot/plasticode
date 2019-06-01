<?php

namespace Plasticode\External;

use Codebird\Codebird;

use Plasticode\Util\Strings;

class Twitter
{
    private $cb;
    
    public function __construct($settings)
    {
        Codebird::setConsumerKey($settings['consumer_key'], $settings['consumer_secret']);
    
        $this->cb = Codebird::getInstance();
        $this->cb->setToken($settings['access_token'], $settings['access_key']);
    }
    
	public function tweet($message)
	{
        return $this->cb->statuses_update([
            'status' => $message
        ]);
	}
	
	public function buildMessage($text, $url = null, $tags = null)
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
