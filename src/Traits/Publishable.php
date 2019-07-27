<?php

namespace Plasticode\Traits;

use Plasticode\Util\Date;

trait Publishable
{
    protected function publishIfNeeded($data)
    {
        if ($this->needsPublish($data)) {
            $data = $this->publish($data);
        }
        
        return $data;
    }
    
    protected function publish($data)
    {
        $data['published_at'] = Date::dbNow();
        return $data;
    }
    
    protected function needsPublish($data)
    {
        return
            isset($data['published']) &&
            $data['published'] == 1 &&
            array_key_exists('published_at', $data) &&
            $data['published_at'] == null;
    }
    
    protected function isJustPublished($item, $data)
    {
        return
            $this->needsPublish($data) &&
            isset($item->published_at) &&
            Date::happened($item->published_at);
    }
}
