<?php

namespace Plasticode\Gallery;

use Plasticode\Contained;
use Plasticode\Exceptions\ApplicationException;
use Plasticode\Gallery\ThumbStrategies\ThumbStrategyInterface;
use Plasticode\IO\File;
use Plasticode\IO\Image;

class Gallery extends Contained
{
    protected $thumbStrategy;
    
	protected $baseDir;

	protected $pictureField;
	protected $pictureTypeField;
	protected $pictureFolder;
	protected $picturePublicFolder;

	public $thumbField;
	protected $thumbTypeField;
	protected $thumbFolder;
	protected $thumbPublicFolder;

	protected $folders;

	public function __construct($container, ThumbStrategyInterface $thumbStrategy, $settings = [])
	{
		parent::__construct($container);
		
		$this->thumbStrategy = $thumbStrategy;
		
		$this->baseDir = $settings['base_dir'];

		$fieldSettings = $settings['fields'];
		$folderSettings = $settings['folders'];

		$this->pictureField = $fieldSettings['picture'] ?? 'picture';
		$this->pictureTypeField = $fieldSettings['picture_type'];
		$this->pictureFolder = $folderSettings['picture']['storage'];
		$this->picturePublicFolder = $folderSettings['picture']['public'];

		$this->thumbField = $fieldSettings['thumb'] ?? 'thumb';
		$this->thumbTypeField = $fieldSettings['thumb_type'];
		$this->thumbFolder = $folderSettings['thumb']['storage'];
		$this->thumbPublicFolder = $folderSettings['thumb']['public'];

		$this->folders = $this->getSettings('folders');
	}
	
	protected function getFolder($folder) : string
	{
		if (!isset($this->folders[$folder])) {
			throw new \InvalidArgumentException('Unknown image folder: ' . $folder);
		}
		
		return $this->folders[$folder];
	}

	protected function getItemUrl($folder, $item, $typeField) : string
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
	public function getPictureUrl($item) : string
	{
		return $this->getItemUrl($this->picturePublicFolder, $item, $this->pictureTypeField);
	}
	
	/**
	 * Get public thumb url.
	 * 
	 * @param array $item
	 * @return string
	 */
	public function getThumbUrl($item) : string
	{
		return $this->getItemUrl($this->thumbPublicFolder, $item, $this->thumbTypeField);
	}

    /**
     * Build image's server path.
     */
	protected function buildImagePath($folder, $name, $imgType) : string
	{
		$path = $this->getFolder($folder);
		$ext = Image::getExtension($imgType) ?? $imgType;

		return $this->baseDir . $path . $name . '.' . $ext;
	}
	
	/**
	 * Get picture server path.
	 */
	public function buildPicturePath($name, $imgType) : string
	{
		return $this->buildImagePath($this->pictureFolder, $name, $imgType);
	}
	
	/**
	 * Get thumb server path.
	 */
	public function buildThumbPath($name, $imgType) : string
	{
		return $this->buildImagePath($this->thumbFolder, $name, $imgType);
	}

	/**
	 * Get picture from save data (API call).
	 * 
	 * @return Image|null
	 */
	public function getPicture($item, $data)
	{
	    $picture = $data[$this->pictureField] ?? null;
	    
	    return $picture
	        ? Image::parseBase64($picture)
	        : null;
	}
	
	/**
	 * Get thumb from save data (API call).
	 * 
	 * @return Image|null
	 */
	protected function getThumb($item, $data)
	{
	    return $this->thumbStrategy->getThumb($this, $item, $data);
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
		$picture = $this->getPicture($item, $data);

		if ($picture && $picture->notEmpty()) {
			$this->savePicture($item, $picture);
			
			// set width / height
			if ($picture->width > 0) {
			    $item->width = $picture->width;
			}
			
			if ($picture->height > 0) {
			    $item->height = $picture->height;
			}
			
			// set avg_color
			$item->avg_color = $this->getAvgColor($item, $picture);
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
	
	public function loadPicture($item) : Image
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
	protected function savePicture($item, Image $picture)
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
	protected function saveThumb($item, Image $thumb)
	{
		$fileName = $this->buildThumbPath($item->id, $thumb->imgType);
		$thumb->save($fileName);

		$mask = $this->buildThumbPath($item->id, '*');
		File::cleanUp($mask, $fileName);
	}
	
	/**
	 * Saves image with auto-generated thumb.
	 * 
	 * @return void
	 */
	public function saveImage($item, Image $picture)
	{
		if (!$picture || $picture->empty()) {
		    throw new \InvalidArgumentException('Gallery.saveImage() can\'t save empty image.');
		}
		
		$this->savePicture($item, $picture);
		
		$thumb = $this->createAndSaveThumb($item, $picture);

		$item = $this->beforeSave($item, $picture, $thumb);

		$item->save();
	}
	
	private function createAndSaveThumb($item, Image $picture)
	{
		$thumb = $this->getThumbFromImage($picture);
		
		if ($thumb && $thumb->notEmpty()) {
			$this->saveThumb($item, $thumb);
		}
		
		return $thumb;
	}
	
	public function delete($item)
	{
		$pictureFileName = $this->buildPicturePath($item->id, $item->{$this->pictureTypeField});
		File::delete($pictureFileName);

		$thumbFileName = $this->buildThumbPath($item->id, $item->{$this->thumbTypeField});
		File::delete($thumbFileName);
	}

	/**
	 * Generates thumb based on image.
	 */
	public function getThumbFromImage(Image $picture)
	{
		$image = $picture->getGdImage();
		
		if ($image === null) {
			return null;
		}

		$thumbImage = $this->thumbStrategy->createThumb($image);

		$thumb = Image::fromGdImage($thumbImage, $picture->imgType);

		imagedestroy($thumbImage);
		imagedestroy($image);
		
		return $thumb;
	}

	/**
	 * Checks if thumb exists, creates it otherwise
	 */
	public function ensureThumbExists($item)
	{
		$thumbPath = $this->buildThumbPath($item->id, $item->{$this->thumbTypeField});
		
		if (File::exists($thumbPath)) {
		    return;
		}
		
		$picture = $this->loadPicture($item);
		
		$this->createAndSaveThumb($item, $picture);
	}

	public function getAvgColor($item, $picture = null)
	{
	    if (!$picture) {
            $picture = $this->loadPicture($item);
	    }
        
        try {
            $rgba = $picture->getAvgColor();
            $color = Image::serializeRGBA($rgba);
        
            return $color;
        }
        catch (\Exception $ex) {
            throw new ApplicationException('Unable to get avg. color. ' . $ex->getMessage());
        }
	}
}
