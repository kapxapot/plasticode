<?php

namespace Plasticode\Data;

use Plasticode\Contained;

class TableRights extends Contained {
	private $table;

	public function __construct($c, $table) {
		parent::__construct($c);
		
		$this->table = $table;
	}
	
	public function get($item = null) {
		$can = $this->access->getAllRights($this->table);
		
		if ($item) {
			$item = is_array($item) ? $item : $item->asArray();
			$own = $this->auth->isOwnerOf($item);
	
			$can['read'] = $can['read'] || ($own && $can['read_own']);
			$can['edit'] = $can['edit'] || ($own && $can['edit_own']);
			$can['delete'] = $can['delete'] || ($own && $can['delete_own']);
		}
		
		return $can;
	}
	
	public function enrichRights($item) {
		if ($item) {
			$rights = $this->get($item);
	
			$item['access']['edit'] = $rights['edit'];
			$item['access']['delete'] = $rights['delete'];
		}

		return $item;
	}
	
	public function canRead($item) {
		$rights = $this->get($item);
		return $rights['read'];
	}
}
