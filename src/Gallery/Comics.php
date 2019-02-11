<?php

namespace Plasticode\Gallery;

use Plasticode\IO\Image;

class Comics extends Gallery
{
	private $thumbHeight;
	
	public function __construct($c, $settings)
	{
		parent::__construct($c, $settings);
		
		$this->thumbHeight = $settings['thumb_height'];
	}

	/**
	 * Get thumb from save data (API call).
	 * 
	 * This is different from Gallery, because thumb is auto-generated on every save.
	 */
	protected function getThumb($item, $data)
	{
		$thumb = null;
		
		$picture = $this->getPicture($data);
		
		if (!$picture && $item->id > 0) {
			$picture = $this->loadPicture($item);
		}
		
		if ($picture && $picture->notEmpty()) {
			$thumb = $this->getThumbFromImage($picture);
		}
		
		return $thumb;
	}
	
	/**
	 * Builds GD image for thumb based on picture GD image.
	 */
	protected function buildThumbImage($image, $width, $height)
	{
		$newHeight = $this->thumbHeight ?? $height;
		$newWidth = $width * $newHeight / $height;

		$thumbImage = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($thumbImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		
		return $thumbImage;
	}
}
