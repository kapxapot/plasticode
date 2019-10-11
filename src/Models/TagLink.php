<?php

namespace Plasticode\Models;

class TagLink extends Model
{
    public function __construct(string $tag, string $tab)
    {
        parent::__construct();
        
        $this->tag = $tag;
        $this->tab = $tab;
    }
    
    // PROPS
    
    public function text() : string
    {
        return $this->tag;
    }
    
    public function url() : string
    {
        return self::$linker->tag($this->tag, $this->tab);
    }
}
