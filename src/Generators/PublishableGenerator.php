<?php

namespace Plasticode\Generators;

use Plasticode\Util\Date;

class PublishableGenerator extends EntityGenerator {
	protected function publishIfNeeded($data) {
		if ($this->needsPublish($data)) {
			$data = $this->publish($data);
		}
		
		return $data;
	}
	
	protected function publish($data) {
		$data['published_at'] = Date::dbNow();
		return $data;
	}
	
	protected function needsPublish($data) {
		return
			isset($data['published']) &&
			$data['published'] == 1 &&
			!isset($data['published_at']);
	}
	
	protected function isJustPublished($item, $data) {
		return
			$this->needsPublish($data) &&
			isset($item->published_at) &&
			Date::happened($item->published_at);
	}
}
