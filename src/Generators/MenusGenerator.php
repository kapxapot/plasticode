<?php

namespace Plasticode\Generators;

class MenusGenerator extends EntityGenerator
{
    public function getRules($data, $id = null)
    {
        $rules = parent::getRules($data, $id);
        
        $rules['link'] = $this->rule('url');
        $rules['text'] = $this->rule('text');
        $rules['position'] = $this->rule('posInt');
        
        return $rules;
    }
}
