<?php

namespace Plasticode\Gallery;

use Plasticode\IO\Image;

class Comics extends Gallery {
	private $thumbHeight;
	
	public function __construct($c, $settings) {
		parent::__construct($c, $settings);
		
		$this->thumbHeight = $settings['thumb_height'];
	}
	
	protected function getThumb($item, $data) {
		$thumb = null;
		
		$picture = $this->getPicture($data);
		
		if (!$picture && $item->id > 0) {
			$picture = $this->loadPicture($item);
		}
		
		if ($picture && $picture->notEmpty()) {
			$data = $picture->data;
			
			$image = imagecreatefromstring($data);
			if ($image === false) {
				throw new \InvalidArgumentException('Error parsing comic page image.');
			}

			list($width, $height) = getimagesizefromstring($data);
			
			$newHeight = $this->thumbHeight;
			$newWidth = $width * $newHeight / $height;

			$thumbImage = imagecreatetruecolor($newWidth, $newHeight);
			imagecopyresampled($thumbImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			
			$thumb = new Image;
			$thumb->data = $this->gdImgToBase64($thumbImage, $picture->imgType);
			$thumb->imgType = $picture->imgType;

			imagedestroy($thumbImage);
			imagedestroy($image);
		}
		
		return $thumb;
	}
	
	private function gdImgToBase64($gdImg, $format = 'jpeg') {
		$data = null;
		
		// known ext?
	    if (self::getExtension($format) !== null) {
	        ob_start();
	
	        if ($format == 'jpeg' ) {
	            imagejpeg($gdImg, null, 99);
	        } elseif ($format == 'png') {
	            imagepng($gdImg);
	        } elseif ($format == 'gif') {
	            imagegif($gdImg);
	        }
	
	        $data = ob_get_contents();

	        ob_end_clean();
	    }
	
	    return $data;
	}
}
