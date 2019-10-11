<?php

namespace Plasticode\Generators;

use Plasticode\Models\MenuItem;

class MenuItemsGenerator extends EntityGenerator
{
    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);
        
        $rules['link'] = $this->rule('url');
        $rules['text'] = $this->rule('text');
        $rules['position'] = $this->rule('posInt');
        
        return $rules;
    }
    
    public function getOptions() : array
    {
        $options = parent::getOptions();
        
        $options['uri'] = 'menus/{id:\d+}/menu_items';
        $options['filter'] = 'menu_id';
        
        return $options;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $menuItem = MenuItem::get($item['id']);

        $item['url'] = $menuItem->url();

        return $item;
    }
    
    public function getAdminParams(array $args) : array
    {
        $params = parent::getAdminParams($args);

        $menuId = $args['id'];
        
        $menu = $this->menuRepository->get($menuId);

        $params['source'] = "menus/{$menuId}/menu_items";
        $params['breadcrumbs'] = [
            [ 'text' => 'Menu', 'link' => $this->router->pathFor('admin.entities.menus') ],
            [ 'text' => $menu->text ],
            [ 'text' => 'Menu Items' ],
        ];
        
        $params['hidden'] = [
            'menu_id' => $menuId,
        ];
        
        return $params;
    }
}
