<?php

namespace Plasticode\Controllers;

use Plasticode\Contained;
use Illuminate\Support\Arr;

class Controller extends Contained {
	protected $autoOneColumn = true;
	
	protected function notFound($request, $response) {
		$handler = $this->notFoundHandler;
		return $handler($request, $response);
	}

	protected function buildParams($settings) {
		$params = [
			'menu' => $this->buildMenu($settings),
		];

		$sidebar = $this->buildSidebar($settings);
		
		if (count($sidebar) > 0) {
			$params['sidebar'] = $sidebar;
		}
		elseif ($this->autoOneColumn === true) {
			$params['one_column'] = 1;
		}
		
		$params['rel_prev'] = Arr::get($settings, 'params.paging.prev.url');
		$params['rel_next'] = Arr::get($settings, 'params.paging.next.url');

		return array_merge($params, $settings['params']);
	}
	
	protected function buildMenu($settings) {
		return $this->builder->buildMenu();
	}
	
	protected function buildSidebar($settings) {
		$result = [];
		
		if (is_array($settings['sidebar'])) {
			foreach ($settings['sidebar'] as $part) {
				$result =
					$this->buildPart($settings, $result, $part) ??
					$this->buildActionPart($result, $part);
			}
		}

		return $result;
	}
	
	protected function buildActionPart($result, $part) {
		$bits = explode('.', $part);
		if (count($bits) > 1) {
			$action = $bits[0];
			$entity = $bits[1];

			Arr::set($result, "actions.{$action}.{$entity}", true);
		}
		else {
			throw new \InvalidArgumentException('Unknown sidebar part: ' . $part);
		}

		return $result;
	}
	
	protected function buildPart($settings, $result, $part) {
		return null;
	}
}
