<?php

namespace Plasticode\Gallery;

use ORM;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Exceptions\InvalidResultException;
use Plasticode\Gallery\ThumbStrategies\Interfaces\ThumbStrategyInterface;
use Plasticode\IO\File;
use Plasticode\IO\Image;
use Plasticode\Models\Basic\DbModel;
use Webmozart\Assert\Assert;

class Gallery
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

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        ThumbStrategyInterface $thumbStrategy,
        array $settings = []
    )
    {
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

        $this->folders = $settingsProvider->get('folders');
    }
    
    protected function getFolder(string $name) : string
    {
        $folder = $this->folders[$name] ?? null;

        Assert::notNull(
            $folder,
            'Unknown image folder: ' . $name
        );
        
        return $folder;
    }

    protected function getItemUrl(string $folder, array $item, string $typeField) : string
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
    public function getPictureUrl(array $item) : string
    {
        return $this->getItemUrl(
            $this->picturePublicFolder,
            $item,
            $this->pictureTypeField
        );
    }
    
    /**
     * Get public thumb url.
     * 
     * @param array $item
     * @return string
     */
    public function getThumbUrl(array $item) : string
    {
        return $this->getItemUrl(
            $this->thumbPublicFolder,
            $item,
            $this->thumbTypeField
        );
    }

    /**
     * Build image's server path.
     */
    protected function buildImagePath(string $folder, string $name, string $imgType) : string
    {
        $path = $this->getFolder($folder);
        $ext = Image::getExtension($imgType) ?? $imgType;

        return $this->baseDir . $path . $name . '.' . $ext;
    }
    
    /**
     * Get picture server path.
     */
    public function buildPicturePath(string $name, string $imgType) : string
    {
        return $this->buildImagePath($this->pictureFolder, $name, $imgType);
    }
    
    /**
     * Get thumb server path.
     */
    public function buildThumbPath(string $name, string $imgType) : string
    {
        return $this->buildImagePath($this->thumbFolder, $name, $imgType);
    }

    /**
     * Get picture from save data (API call)
     * 
     * @return Image|null
     */
    public function getPicture(ORM $item, array $data) : ?Image
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
    protected function getThumb(ORM $item, array $data) : ?Image
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
     * @param ORM|array $item
     * @param array $data
     * @return void
     */
    public function save($item, array $data) : void
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
            
            $item->avg_color = $this->getAvgColor($item, $picture);
        }
        
        $thumb = $this->getThumb($item, $data);
        
        if ($thumb && $thumb->notEmpty()) {
            $this->saveThumb($item, $thumb);
        }

        $item = $this->beforeSave($item, $picture, $thumb);
        
        // todo: this is bad / to be removed
        $item->save();
    }

    protected function beforeSave(ORM $item, ?Image $picture = null, ?Image $thumb = null) : ORM
    {
        if ($picture && $picture->notEmpty()) {
            $item->{$this->pictureTypeField} = $picture->imgType;
        }
        
        if ($this->pictureTypeField != $this->thumbTypeField
            && $thumb
            && $thumb->notEmpty()
        ) {
            $item->{$this->thumbTypeField} = $thumb->imgType;
        }
        
        return $item;
    }
    
    /**
     * @param DbModel|ORM $item
     * @return Image
     */
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
     */
    protected function savePicture(ORM $item, Image $picture) : void
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
     */
    protected function saveThumb(ORM $item, Image $thumb) : void
    {
        $fileName = $this->buildThumbPath($item->id, $thumb->imgType);
        $thumb->save($fileName);

        $mask = $this->buildThumbPath($item->id, '*');
        File::cleanUp($mask, $fileName);
    }
    
    /**
     * Saves image with auto-generated thumb.
     */
    public function saveImage(ORM $item, Image $picture) : void
    {
        Assert::true(
            $picture && !$picture->empty(),
            'Gallery.saveImage() can\'t save empty image.'
        );
        
        $this->savePicture($item, $picture);
        
        $thumb = $this->createAndSaveThumb($item, $picture);
        $item = $this->beforeSave($item, $picture, $thumb);

        // todo: this is bad / to be removed
        $item->save();
    }
    
    private function createAndSaveThumb(ORM $item, Image $picture) : ?Image
    {
        $thumb = $this->getThumbFromImage($picture);
        
        if ($thumb && $thumb->notEmpty()) {
            $this->saveThumb($item, $thumb);
        }
        
        return $thumb;
    }
    
    /**
     * Delete picture
     *
     * @param ORM|array $item
     * @return void
     */
    public function delete($item) : void
    {
        $pictureFileName = $this->buildPicturePath(
            $item['id'],
            $item[$this->pictureTypeField]
        );

        File::delete($pictureFileName);

        $thumbFileName = $this->buildThumbPath(
            $item['id'],
            $item[$this->thumbTypeField]
        );

        File::delete($thumbFileName);
    }

    /**
     * Generates thumb based on image.
     */
    public function getThumbFromImage(Image $picture) : ?Image
    {
        $image = $picture->getGdImage();
        
        if (is_null($image)) {
            return null;
        }

        $thumbImage = $this->thumbStrategy->createThumb($image);

        $thumb = Image::fromGdImage($thumbImage, $picture->imgType);

        imagedestroy($thumbImage);
        imagedestroy($image);
        
        return $thumb;
    }

    /**
     * Checks if thumb exists, creates it otherwise.
     * 
     * @param mixed $item
     * @return void
     */
    public function ensureThumbExists($item) : void
    {
        $thumbPath = $this->buildThumbPath(
            $item->id,
            $item->{$this->thumbTypeField}
        );
        
        if (File::exists($thumbPath)) {
            return;
        }
        
        $picture = $this->loadPicture($item);
        
        $this->createAndSaveThumb($item, $picture);
    }

    /**
     * Calculates the average RBGA color for the picture.
     *
     * @param DbModel|ORM $item
     * @param Image|null $picture
     * @return string
     */
    public function getAvgColor($item, ?Image $picture = null) : string
    {
        $picture = $picture ?? $this->loadPicture($item);
        
        try {
            $rgba = $picture->getAvgColor();
            $color = Image::serializeRGBA($rgba);
        
            return $color;
        }
        catch (\Exception $ex) {
            throw new InvalidResultException(
                'Unable to get avg. color. ' .
                $ex->getMessage()
            );
        }
    }
}
