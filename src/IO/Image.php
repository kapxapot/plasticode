<?php

namespace Plasticode\IO;

use Plasticode\Exceptions\ApplicationException;

class Image
{
	public $data;
	public $imgType;
	
	public function __construct($data = null, $imgType = null)
	{
	    $this->data = $data;
	    $this->imgType = $imgType;
	}

	const IMAGE_TYPES = [
		'jpeg' => 'jpg',
		'png' => 'png',
		'gif' => 'gif',
	];

	public static function getExtension($type)
	{
		return self::IMAGE_TYPES[$type] ?? null;
	}
	
	public static function getImageTypeFromPath($path)
	{
	    $ext = File::getExtension($path);
	    return array_search($ext, self::IMAGE_TYPES);
	}
	
	public static function isValidImageType($type)
	{
	    return self::getExtension($type) !== null;
	}
	
	public static function isValidExtension($ext)
	{
	    return in_array($ext, self::IMAGE_TYPES);
	}
	
	public static function isImagePath($path)
	{
	    $ext = File::getExtension($path);
	    return $ext && self::isValidExtension($ext);
	}

	/**
	 * Build mime-type string.
	 * 
	 * image/jpeg, image/png, image/gif
	 */
	public static function buildTypesString()
	{
		$parts = [];
		
		foreach (array_keys(self::IMAGE_TYPES) as $type) {
			$parts[] = 'image/' . $type;
		}
		
		return implode(', ', $parts);
	}

	public static function parseBase64($base64)
	{
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
	
	static public function fromString($imgString, $imgType = 'jpeg')
	{
	}
	
	static public function load($fileName, $imgType)
	{
		$img = new Image;
		$img->imgType = $imgType;
		
		try {
		    $img->data = File::load($fileName);
		} catch (\Exception $ex) {
		    // ..
		}

		return $img;
	}

	public function notEmpty()
	{
		return strlen($this->data) > 0;
	}
	
	public function isValid()
	{
	    return self::isValidImageType($this->imgType);
	}
	
	public function save($fileName)
	{
		if ($this->notEmpty()) {
			File::save($fileName, $this->data);
		}
		else {
			throw new ApplicationException('No data to save.');
		}
	}
}
