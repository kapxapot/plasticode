<?php

namespace Plasticode\Gallery\ThumbStrategies;

use Plasticode\Gallery\Gallery;

interface ThumbStrategyInterface
{
	/**
	 * Get thumb from save data (API call).
	 * 
	 * @return Image|null
	 */
	public function getThumb(Gallery $gallery, $item, $data);

	public function createThumb($image);
}
