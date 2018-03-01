<?php

namespace Plasticode\IO;

use Plasticode\Exceptions\ApplicationException;

class Image {
	public $data;
	public $imgType;
	
	static public function parseBase64($base64) {
		$img = new Image;
		
		if (preg_match("#^data:image/(\w+);base64,(.*)$#i", $base64, $matches)) {
			$imgType = $matches[1];
			$data = $matches[2];

			if (strlen($data) > 0) {
				$img->data = base64_decode($data);
				$img->imgType = $imgType;
			}
			/*else {
				throw \InvalidArgumentException('No image.');
			}*/
		}
		
		return $img;
	}
	
	static public function load($fileName, $imgType) {
		$img = new Image;
		$img->imgType = $imgType;
		
		try {
			$img->data = File::load($fileName);
		}
		catch (\Exception $e) {
		}
		
		return $img;
	}

	public function notEmpty() {
		return strlen($this->data) > 0;
	}
	
	public function save($fileName) {
		if ($this->notEmpty()) {
			File::save($fileName, $this->data);
		}
		else {
			throw new ApplicationException('No data to save.');
		}
	}
}
