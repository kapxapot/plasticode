<?php

namespace Plasticode\Generators;

use Plasticode\Models\Menu;

class MenusGenerator extends EntityGenerator
{
    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);
        
        $rules['link'] = $this->rule('url');
        $rules['text'] = $this->rule('text');
        $rules['position'] = $this->rule('posInt');
        
        return $rules;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $menu = Menu::get($item['id']);

        $item['url'] = $menu->url();

        return $item;
    }
}
