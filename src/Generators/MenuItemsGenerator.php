<?php

namespace Plasticode\Generators;

class MenuItemsGenerator extends EntityGenerator
{
    public function getRules($data, $id = null)
    {
        $rules = parent::getRules($data, $id);
        
        $rules['link'] = $this->rule('url');
        $rules['text'] = $this->rule('text');
        $rules['position'] = $this->rule('posInt');
        
        return $rules;
    }
    
    public function getOptions()
    {
        $options = parent::getOptions();
        
        $options['uri'] = 'menus/{id:\d+}/menu_items';
        $options['filter'] = 'menu_id';
        
        return $options;
    }
    
    public function getAdminParams($args)
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
