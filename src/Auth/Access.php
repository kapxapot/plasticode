<?php

namespace Plasticode\Auth;

use Plasticode\Contained;
use Plasticode\Exceptions\ApplicationException;

class Access extends Contained {
	private $actions;
	private $templates;
	private $rights;
	
	public function __construct($container) {
		parent::__construct($container);
		
		$settings = $this->getSettings('access');
		
		$this->actions = $this->flattenActions($settings['actions']);

		$this->templates = $settings['templates'];
		$this->rights = $settings['rights'];
	}
	
	private function flattenActions($tree, $path = [], $flat = []) {
		$add = function($node) use ($path, &$flat) {
			$path[] = $node;
			$flat[$node] = $path;
			
			return $path;
		};
		
		foreach ($tree as $node) {
			if (is_array($node)) {
				foreach ($node as $nodeTitle => $nodeTree) {
					$pathCopy = $add($nodeTitle);
		
					$flat = $this->flattenActions($nodeTree, $pathCopy, $flat);
				}
			}
			else {
				$add($node);
			}
		}
		
		return $flat;
	}

	public function checkRights($entity, $action) {
		if (!isset($this->actions[$action])) {
			throw new \InvalidArgumentException('Unknown action: ' . $action);
		}
		
		$grantAccess = false;
		
		$role = $this->auth->getRole();
		$roleTag = $role->tag;
		
		if (!isset($this->rights[$entity])) {
			throw new ApplicationException('You must configure access rights for entity "' . $entity . '"');
		}
		
		$rights = $this->rights[$entity];

		foreach ($this->actions[$action] as $actionBit) {
			$grantAccess = $this->checkRightsForExactAction($rights, $actionBit, $roleTag);
			if ($grantAccess) {
				break;
			}
		}

		return $grantAccess;
	}
	
	private function checkRightsForExactAction($rights, $action, $roleTag) {
		$grantAccess = false;

		if (isset($rights['template'])) {
			$tname = $rights['template'];

			if (!isset($this->templates[$tname])) {
				throw new ApplicationException('Unknown access rights template: ' . $tname);
			}
			
			$template = $this->templates[$tname];
			
			$grantAccess = isset($template[$roleTag]) && in_array($action, $template[$roleTag]);
		}
		
		if (!$grantAccess) {
 			$grantAccess = isset($rights[$roleTag]) && in_array($action, $rights[$roleTag]);
 		}

		return $grantAccess;
	}
	
	public function getAllRights($entity) {
		$path = "access.{$entity}";
		$can = $this->cache->get($path);

		if ($can === null) {
			$can = [];
			$rights = array_keys($this->actions);
			foreach ($rights as $r) {
				$can[$r] = $this->checkRights($entity, $r);
			}

			$this->cache->set($path, $can);
		}
		
		return $can;
	}
}
