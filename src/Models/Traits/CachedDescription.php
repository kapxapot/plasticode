<?php

namespace Plasticode\Models\Traits;

use Plasticode\Parsing\ParsingContext;
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

    public function parsedDescription() : ?ParsingContext
    {
        return $this->lazy(
            function () {
                $descriptionField = static::getDescriptionField();
                $cacheField = static::getDescriptionCacheField();
                
                $description = $this->{$descriptionField};
                $cache = $this->{$cacheField};

                if (strlen($description) == 0) {
                    return null;
                }

                if (strlen($cache) > 0) {
                    $parsed = @json_decode($cache);
                }
                
                if ($parsed instanceof ParsingContext) {
                    $updatedAt = $parsed->updatedAt;
                    
                    if (is_null($updatedAt) ||
                        Date::expired($updatedAt, static::getDescriptionTTL())
                    ) {
                        unset($parsed);
                    }
                }

                if (!($parsed instanceof ParsingContext)) {
                    $parsed = self::$parser->parse($description);
                    $parsed->updatedAt = Date::dbNow();
                    
                    $this->{$cacheField} = json_encode($parsed);
                    $this->save();
                }
                
                if (!is_null($parsed)) {
                    $parsed = self::$parser->renderLinks($parsed);
                }

                return $parsed;
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
