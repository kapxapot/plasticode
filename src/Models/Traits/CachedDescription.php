<?php

namespace Plasticode\Models\Traits;

trait CachedDescription
{
    protected static function getDescriptionField()
    {
        return 'description';
    }
    
    protected static function getDescriptionCacheField()
    {
        return 'cache';
    }

    public function parsedDescription()
    {
        return $this->lazy(__FUNCTION__, function () {
            $descriptionField = static::getDescriptionField();
            $cacheField = static::getDescriptionCacheField();
            
            $description = $this->{$descriptionField};
            $cache = $this->{$cacheField};

            if (strlen($description) > 0) {
                if (strlen($cache) > 0) {
                    $parsed = @json_decode($cache, true);
                }
    
                //debug_print_backtrace(0, 5);
                //var_dump("1 " . (is_null($parsed) ? "null" : "not null")); // not null
    
                if (!is_array($parsed)) {
                    $parsed = self::$parser->parse($description);
                    
                    $this->{$cacheField} = json_encode($parsed);
                    $this->save();
                }
                
                if (is_array($parsed)) {
                    $parsed['text'] = self::$parser->renderLinks($parsed['text']);
                }
            }

            return $parsed ?? [];
        });
    }

    public function resetDescription()
    {
        $cacheField = static::getDescriptionCacheField();
        
        $this->{$cacheField} = null;
        $this->save();
        
        $this->resetLazy('parsedDescription');
    }
}

