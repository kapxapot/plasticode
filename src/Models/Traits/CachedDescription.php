<?php

namespace Plasticode\Models\Traits;

use Plasticode\Util\Date;

trait CachedDescription
{
    protected static function getDescriptionField() : string
    {
        return 'description';
    }
    
    protected static function getDescriptionCacheField() : string
    {
        return 'cache';
    }
    
    protected static function getDescriptionTTL() : string
    {
        return '1 hour';
    }

    public function parsedDescription() : array
    {
        return $this->lazy(
            function () {
                $descriptionField = static::getDescriptionField();
                $cacheField = static::getDescriptionCacheField();
                
                $description = $this->{$descriptionField};
                $cache = $this->{$cacheField};

                if (strlen($description) > 0) {
                    if (strlen($cache) > 0) {
                        $parsed = @json_decode($cache, true);
                    }
                    
                    if (is_array($parsed)) {
                        $updatedAt = $parsed['updated_at'] ?? null;
                        
                        if (!$updatedAt
                            || Date::expired(
                                $updatedAt, static::getDescriptionTTL()
                            )
                        ) {
                            unset($parsed);
                        }
                    }

                    if (!is_array($parsed)) {
                        $parsed = self::$parser->parse($description);
                        $parsed['updated_at'] = Date::dbNow();
                        
                        $this->{$cacheField} = json_encode($parsed);
                        $this->save();
                    }
                    
                    if (is_array($parsed)) {
                        $parsed['text'] = self::$parser->renderLinks($parsed['text']);
                    }
                }

                return $parsed ?? [];
            }
        );
    }

    public function resetDescription() : void
    {
        $cacheField = static::getDescriptionCacheField();
        
        $this->{$cacheField} = null;
        $this->save();
        
        $this->resetLazy('parsedDescription');
    }
}

