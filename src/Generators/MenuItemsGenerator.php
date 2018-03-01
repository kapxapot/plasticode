<?php

namespace Plasticode\Generators;

class MenuItemsGenerator extends EntityGenerator {
	public function getRules($data, $id = null) {
		return [
			'link' => $this->rule('url'),
			'text' => $this->rule('text'),
			'position' => $this->rule('posInt'),
		];
	}
	
	public function getOptions() {
		return [
			'uri' => 'menus/{id:\d+}/menu_items',
			'filter' => 'menu_id',
		];
	}
	
	public function getAdminParams($args) {
		$params = parent::getAdminParams($args);

		$menuId = $args['id'];
		$menu = $this->db->getEntityById('menus', $menuId);

		$params['source'] = "menus/{$menuId}/menu_items";
		$params['breadcrumbs'] = [
			[ 'text' => 'Menu', 'link' => $this->router->pathFor('admin.entities.menus') ],
			[ 'text' => $menu['text'] ],
			[ 'text' => 'Menu Items' ],
		];
		
		$params['hidden'] = [
			'menu_id' => $menuId,
		];
		
		return $params;
	}
}
