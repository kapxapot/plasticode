<?php

namespace Plasticode\Data;

use Plasticode\Contained;
use Plasticode\Exceptions\ApplicationException;

class TableRights extends Contained {
	public $table;

	public function __construct($c, $table) {
		parent::__construct($c);
		
		$this->table = $table;
	}
	
	public function get($item = null) {
		try {
			$can = $this->access->getAllRights($this->table);
		
			if ($item) {
				$item = is_array($item) ? $item : $item->asArray();
	
				$noOwner = !isset($item['created_by']);
				$own = $this->auth->isOwnerOf($item);
		
				$can['read'] = $noOwner || $can['read'] || ($own && $can['read_own']);
				$can['edit'] = $can['edit'] || ($own && $can['edit_own']);
				$can['delete'] = $can['delete'] || ($own && $can['delete_own']);
			}
		}
		catch (ApplicationException $ex) {
			// some tables don't have rights
			// that's ok HERE
		}

		return $can;
	}
	
	public function enrichRights($item) {
		if ($item) {
			$rights = $this->get($item);
	
			if ($rights) {
				$item['access']['edit'] = $rights['edit'];
				$item['access']['delete'] = $rights['delete'];
			}
		}

		return $item;
	}
	
	public function canRead($item) {
		$rights = $this->get($item);
		return $rights['read'];
	}
}
