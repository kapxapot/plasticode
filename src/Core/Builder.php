<?php

namespace Plasticode\Core;

use Plasticode\Contained;
use Plasticode\Exceptions\ApplicationException;
use Plasticode\Util\Sort;
use Plasticode\Util\Strings;

class Builder extends Contained {
	protected $sort;

	public function __construct($container) {
		parent::__construct($container);
		
		$this->sort = new Sort;
	}

	protected function year($date) {
		return strftime('%Y', $date);
	}

	protected function month($date) {
		return strftime('%m', $date);
	}
	
	protected function formatDate($date) {
		return strftime($this->getSettings('date_format'), $date);
	}
	
	protected function formatDateTime($date) {
		return strftime($this->getSettings('time_format'), $date);
	}
	
	protected function sortByDate($items, $field = 'pub_date') {
		return $this->sort->multiSort($items, [
			$field => [ 'dir' => 'desc' ],
		]);
	}
	
	protected function trunc($text, $limitVar)
	{
	    $limit = $this->getSettings($limitVar);
	    
	    if (!$limit) {
	        throw new ApplicationException('No limit settings found: ' . $limitVar);
	    }
	    
	    return Strings::trunc($text, $limit);
	}
	
	protected function buildSubMenus($menus) {
		return array_map(function($menu) {
			$menu['items'] = $this->db->getMenuItems($menu['id']);
			return $menu;
		}, $menus ?? []);
	}
	
	public function buildMenu() {
		$menus = $this->db->getMenus();
		
		return $this->buildSubMenus($menus);
	}

	public function buildComplexPaging($url, $count, $index, $pageSize) {
		$paging = [];
		$pages = [];
		
		$stepping = 1;
		$neighbours = 7;
		
		if ($count > $pageSize) {
			// prev page
			if ($index > 1) {
        		$prev = $this->buildPage($url, $index - 1, false, $this->decorator->prev(), 'Предыдущая страница');
        		$pages[] = $prev;
        		$paging['prev'] = $prev;
			}

			$pageCount = ceil($count / $pageSize);
			
			$shownIndex = 1;
			$step = ceil($pageCount / $stepping);

			while ($shownIndex <= $pageCount) {
				if ($shownIndex == 1 ||
					$shownIndex >= $pageCount ||
					($shownIndex % $step == 0) ||
					(abs($shownIndex - $index) <= $neighbours)) {
					$pages[] = $this->buildPage($url, $shownIndex, $shownIndex == $index);
				}
				
				$shownIndex++;
			}

			// next page
			if ($index < $pageCount) {
				$next = $this->buildPage($url, $index + 1, false, $this->decorator->next(), 'Следующая страница');
				$pages[] = $next;
				$paging['next'] = $next;
			}
			
			$paging['pages'] = $pages;
		}
		
		return $paging;
	}
	
	protected function stamps($data, $shortDates = false) {
		if ($data['created_by']) {
			$row = $this->db->getUser($data['created_by']);
			$data['author'] = $this->buildUser($row);
		}
		
		if ($data['updated_by']) {
			$row = $this->db->getUser($data['updated_by']);
			$data['editor'] = $this->buildUser($row);
		}
		
		$dateFields = [ 'created_at', 'published_at', 'updated_at', 'starts_at', 'ends_at' ];
		$func = $shortDates ? 'formatDate' : 'formatDateTime';
		
		foreach ($dateFields as $dateField) {
			if (isset($data[$dateField])) {
				$data[$dateField] = $this->{$func}(strtotime($data[$dateField]));
			}
		}

		return $data;
	}

	public function buildPage($baseUrl, $page, $current, $label = null, $title = null) {
		return [
			'page' => $page,
			'current' => $current,
			'url' => $this->linker->page($baseUrl, $page),
			'label' => ($label != null) ? $label : $page,
			'title' => ($title != null) ? $title : "Страница {$page}",
		];
	}

	public function buildPaging($baseUrl, $totalPages, $page) {
		if ($totalPages > 1) {
			$paging = [];
			$pages = [];
			
			if ($page > 1) {
				$prev = $this->buildPage($baseUrl, $page - 1, false, $this->decorator->prev(), 'Предыдущая страница');
				$paging['prev'] = $prev;
				$pages[] = $prev;
			}

			for ($i = 1; $i <= $totalPages; $i++) {
				$pages[] = $this->buildPage($baseUrl , $i, $i == $page);
			}
			
			if ($page < $totalPages) {
				$next = $this->buildPage($baseUrl, $page + 1, false, $this->decorator->next(), 'Следующая страница');
				$paging['next'] = $next;
				$pages[] = $next;
			}
			
			$paging['page'] = $page;
			$paging['pages'] = $pages;

			return $paging;
		}
	}

	public function buildUser($row) {
		$user = $row;
		
		$user['name'] = $user['name'] ?? $user['login'];

		return $user;
	}
	
	// TAGS
	
	protected function tags($tags, $tab = null)
	{
		if (strlen($tags) > 0) {
			$result = array_map(function($t) use ($tab) {
				$tag = trim($t);
				
				return [
					'text' => $tag,
					'url' => $this->linker->tag($tag, $tab),
				];
			}, explode(',', $tags));
		}
		
		return $result;
	}
}
