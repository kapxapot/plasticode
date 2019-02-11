<?php

namespace Plasticode\Models;

class TagLink extends Model
{
    public function __construct($tag, $tab)
    {
        parent::__construct();
        
        $this->tag = $tag;
        $this->tab = $tab;
    }
    
    // PROPS
    
    public function text()
    {
        return $this->tag;
    }
    
    public function url()
    {
        return self::$linker->tag($this->tag, $this->tab);
    }
}
