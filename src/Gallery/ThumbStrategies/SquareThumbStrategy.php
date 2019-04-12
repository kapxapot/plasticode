<?php

namespace Plasticode\Gallery\ThumbStrategies;

use Plasticode\Gallery\Gallery;
use Plasticode\IO\Image;

class SquareThumbStrategy implements ThumbStrategyInterface
{
    /**
     * Size to resize to. Final thumb size.
     */
    private $thumbSize;
    
    public function __construct(int $thumbSize = null)
    {
        $this->thumbSize = $thumbSize;
    }
    
	/**
	 * Get thumb from save data (API call).
	 * 
	 * @return Image|null
	 */
	public function getThumb(Gallery $gallery, $item, $data)
	{
	    $thumb = $data[$gallery->thumbField] ?? null;
	    
		return $thumb
		    ? Image::parseBase64($thumb)
		    : null;
	}
    
	public function createThumb($image)
	{
	    $width = imagesx($image);
	    $height = imagesy($image);

		$srcSize = min($width, $height);
		$thumbSize = min($this->thumbSize ?? $srcSize, $srcSize);

		$xOffset = intdiv($width - $srcSize, 2);
		$yOffset = intdiv($height - $srcSize, 2);

		$thumbImage = imagecreatetruecolor($thumbSize, $thumbSize);
		imagecopyresampled($thumbImage, $image, 0, 0, $xOffset, $yOffset, $thumbSize, $thumbSize, $srcSize, $srcSize);
		
		return $thumbImage;
	}
}
