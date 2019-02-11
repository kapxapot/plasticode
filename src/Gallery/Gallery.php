<?php

namespace Plasticode\Gallery;

use Plasticode\Contained;
use Plasticode\IO\File;
use Plasticode\IO\Image;

class Gallery extends Contained
{
    /**
     * Moved to IO\Image!
     */
	/*const IMAGE_TYPES = [
		'jpeg' => 'jpg',
		'png' => 'png',
		'gif' => 'gif',
	];*/
	
	protected $baseDir;

	protected $pictureField;
	protected $pictureTypeField;

	protected $thumbTypeField;
	protected $thumbField;

	protected $pictureFolder;
	protected $picturePublicFolder;

	protected $thumbFolder;
	protected $thumbPublicFolder;

	protected $folders;

	public function __construct($container, $settings = [])
	{
		parent::__construct($container);
		
		$this->baseDir = $settings['base_dir'];

		$fieldSettings = $settings['fields'];

		$this->pictureField = $fieldSettings['picture'] ?? 'picture';
		$this->pictureTypeField = $fieldSettings['picture_type'];

		$this->thumbField = $fieldSettings['thumb'] ?? 'thumb';
		$this->thumbTypeField = $fieldSettings['thumb_type'];

		$folderSettings = $settings['folders'];
		
		$this->pictureFolder = $folderSettings['picture']['storage'];
		$this->picturePublicFolder = $folderSettings['picture']['public'];
		
		$this->thumbFolder = $folderSettings['thumb']['storage'];
		$this->thumbPublicFolder = $folderSettings['thumb']['public'];

		$this->folders = $this->getSettings('folders');
	}
	
	protected function getFolder($folder)
	{
		if (!isset($this->folders[$folder])) {
			throw new \InvalidArgumentException('Unknown image folder: ' . $folder);
		}
		
		return $this->folders[$folder];
	}
	
	/**
	 * Use Image::getExtension().
	 */
	/*public static function getExtension($type)
	{
		return Image::IMAGE_TYPES[$type] ?? null;
	}*/
	
	protected function getUrl($folder, $item, $typeField)
	{
		$path = $this->getFolder($folder);
		$ext = Image::getExtension($item[$typeField]);
		
		return $path . $item['id'] . '.' . $ext;
	}
	
	/**
	 * Get public picture url.
	 * 
	 * @param array $item
	 * @return string
	 */
	public function getPictureUrl($item)
	{
		return $this->getUrl($this->picturePublicFolder, $item, $this->pictureTypeField);
	}
	
	/**
	 * Get public thumb url.
	 * 
	 * @param array $item
	 * @return string
	 */
	public function getThumbUrl($item)
	{
		return $this->getUrl($this->thumbPublicFolder, $item, $this->thumbTypeField);
	}

	protected function buildImagePath($folder, $name, $imgType)
	{
		$path = $this->getFolder($folder);
		$ext = Image::getExtension($imgType) ?? $imgType;

		return $this->baseDir . $path . $name . '.' . $ext;
	}
	
	/**
	 * Get picture server path.
	 */
	public function buildPicturePath($name, $imgType)
	{
		return $this->buildImagePath($this->pictureFolder, $name, $imgType);
	}
	
	/**
	 * Get thumb server path.
	 */
	public function buildThumbPath($name, $imgType)
	{
		return $this->buildImagePath($this->thumbFolder, $name, $imgType);
	}

	/**
	 * Use Image::buildTypesString().
	 */
	/*public static function buildTypesString()
	{
		$parts = [];
		
		foreach (array_keys(Image::IMAGE_TYPES) as $type) {
			$parts[] = 'image/' . $type;
		}
		
		return implode(', ', $parts);
	}*/
	
	/**
	 * Get picture from save data (API call).
	 */
	protected function getPicture($data)
	{
		$picture = null;
		
		if (array_key_exists($this->pictureField, $data)) {
			$picture = Image::parseBase64($data[$this->pictureField]);
		}

		return $picture;
	}
	
	/**
	 * Get thumb from save data (API call).
	 */
	protected function getThumb($item, $data)
	{
		$thumb = null;
		
		if (array_key_exists($this->thumbField, $data)) {
			$thumb = Image::parseBase64($data[$this->thumbField]);
		}
		
		return $thumb;
	}
	
	/**
	 * Save picture.
	 * 
	 * If we resave thumb, we don't need to save picture again.
	 * In this scenario 'picture' is empty.
	 * 
	 * 'Thumb' can be null too, we don't resave it then.
	 * 
	 * @return void
	 */
	public function save($item, $data)
	{
		$picture = $this->getPicture($data);
		if ($picture && $picture->notEmpty()) {
			$this->savePicture($item, $picture);
		}
		
		$thumb = $this->getThumb($item, $data);
		if ($thumb && $thumb->notEmpty()) {
			$this->saveThumb($item, $thumb);
		}
		
		$item = $this->beforeSave($item, $picture, $thumb);
		
		$item->save();
	}

	protected function beforeSave($item, $picture, $thumb)
	{
		if ($picture && $picture->notEmpty()) {
			$item->{$this->pictureTypeField} = $picture->imgType;
		}
		
		if ($this->pictureTypeField != $this->thumbTypeField && $thumb && $thumb->notEmpty()) {
			$item->{$this->thumbTypeField} = $thumb->imgType;
		}
		
		return $item;
	}
	
	protected function loadPicture($item)
	{
		$imgType = $item->{$this->pictureTypeField};
		$fileName = $this->buildPicturePath($item->id, $imgType);
		
		return Image::load($fileName, $imgType);
	}

	/**
	 * Save picture.
	 * 
	 * Clean previous version if extension was changed.
	 * 
	 * @return void
	 */
	protected function savePicture($item, $picture)
	{
		$fileName = $this->buildPicturePath($item->id, $picture->imgType);
		$picture->save($fileName);

		$mask = $this->buildPicturePath($item->id, '*');
		File::cleanUp($mask, $fileName);
	}
	
	/**
	 * Save thumb.
	 * 
	 * Clean previous version if extension was changed.
	 * 
	 * @return void
	 */
	protected function saveThumb($item, $thumb)
	{
		$fileName = $this->buildThumbPath($item->id, $thumb->imgType);
		$thumb->save($fileName);

		$mask = $this->buildThumbPath($item->id, '*');
		File::cleanUp($mask, $fileName);
	}
	
	/**
	 * Saves image with auto-generated thumb.
	 */
	public function saveImage($item, $picture)
	{
		if ($picture && $picture->notEmpty()) {
			$this->savePicture($item, $picture);
		}
		
		$thumb = $this->getThumbFromImage($picture);
		
		if ($thumb && $thumb->notEmpty()) {
			$this->saveThumb($item, $thumb);
		}
		
		$item = $this->beforeSave($item, $picture, $thumb);
		
		$item->save();
	}
	
	public function delete($item)
	{
		$pictureFileName = $this->buildPicturePath($item->id, $item->{$this->pictureTypeField});
		File::delete($pictureFileName);

		$thumbFileName = $this->buildThumbPath($item->id, $item->{$this->thumbTypeField});
		File::delete($thumbFileName);
	}
	
	/**
	 * Converts GD Image to Base64.
	 */
	protected function gdImgToBase64($gdImg, $format = 'jpeg')
	{
		$data = null;
		
		// known ext?
	    if (Image::getExtension($format) !== null) {
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
	
	/**
	 * Generates thumb based on image.
	 */
	protected function getThumbFromImage($picture)
	{
	    $data = $picture->data;
	    $imgType = $picture->imgType;
	    
		$image = imagecreatefromstring($data);
		if ($image === false) {
			throw new \InvalidArgumentException('Error parsing gallery image.');
		}

		list($width, $height) = getimagesizefromstring($data);
		
		$thumbImage = $this->buildThumbImage($image, $width, $height);
		
		$thumbData = $this->gdImgToBase64($thumbImage, $imgType);
		$thumb = new Image($thumbData, $imgType);

		imagedestroy($thumbImage);
		imagedestroy($image);
		
		return $thumb;
	}
	
	/**
	 * Builds GD image for thumb based on picture GD image.
	 */
	protected function buildThumbImage($image, $width, $height)
	{
		$newWidth = min($width, $height);
		$newHeight = $newWidth;
		
		$x = intdiv($width - $newWidth, 2);
		$y = intdiv($height - $newHeight, 2);

		$thumbImage = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($thumbImage, $image, 0, 0, $x, $y, $newWidth, $newHeight, $newWidth, $newHeight);
		
		return $thumbImage;
	}
}
