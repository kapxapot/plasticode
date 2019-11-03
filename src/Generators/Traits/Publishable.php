<?php

namespace Plasticode\Generators\Traits;

use Plasticode\Util\Date;

trait Publishable
{
    private function publishIfNeeded(array $data) : array
    {
        if ($this->needsPublish($data)) {
            $data = $this->publish($data);
        }
        
        return $data;
    }
    
    protected function publish(array $data) : array
    {
        $data['published_at'] = Date::dbNow();

        return $data;
    }

    protected function needsPublish(array $data) : bool
    {
        return
            isset($data['published']) &&
            $data['published'] == 1 &&
            array_key_exists('published_at', $data) &&
            $data['published_at'] == null;
    }

    protected function isJustPublished(array $item, array $data) : bool
    {
        return
            $this->needsPublish($data) &&
            isset($item['published_at']) &&
            Date::happened($item['published_at']);
    }

    // generators overrides

    public function beforeSave(array $data, $id = null) : array
    {
        $data = parent::beforeSave($data, $id);

        $data = $this->publishIfNeeded($data);

        return $data;
    }
}
