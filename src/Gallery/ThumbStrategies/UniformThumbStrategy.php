<?php

namespace Plasticode\Gallery\ThumbStrategies;

use Plasticode\Gallery\Gallery;

class UniformThumbStrategy implements ThumbStrategyInterface
{
    /**
     * Height to resize to. Final thumb height.
     */
    private $thumbHeight;
    
    public function __construct(int $thumbHeight = null)
    {
        $this->thumbHeight = $thumbHeight;
    }

	/**
	 * Get thumb from save data (API call).
	 * 
	 * This is different from Gallery, because thumb is auto-generated on every save.
	 */
	public function getThumb(Gallery $gallery, $item, $data)
	{
		$thumb = null;
		
		$picture = $gallery->getPicture($item, $data);
		
		if (!$picture && $item->id > 0) {
			$picture = $gallery->loadPicture($item);
		}
		
		if ($picture && $picture->notEmpty()) {
			$thumb = $gallery->getThumbFromImage($picture);
		}
		
		return $thumb;
	}
    
	public function createThumb($image)
	{
	    $width = imagesx($image);
	    $height = imagesy($image);

		$thumbHeight = min($this->thumbHeight ?? $height, $height);
		$thumbWidth = $width * $thumbHeight / $height;

		$thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
		imagecopyresampled($thumbImage, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
		
		return $thumbImage;
	}
}
