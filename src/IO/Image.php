<?php

namespace Plasticode\IO;

use Plasticode\Exceptions\InvalidArgumentException;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Exceptions\InvalidOperationException;
use Plasticode\Exceptions\InvalidResultException;

class Image
{
    public $data;
    public $imgType;
    
    public $width = 0;
    public $height = 0;
    
    public function __construct(string $data = null, string $imgType = null)
    {
        $this->imgType = $imgType;
        $this->data = $data;

        $this->updateWidthHeight();
    }
    
    public static function makeEmpty() : self
    {
        return new Image();
    }

    private function updateWidthHeight()
    {
        if ($this->notEmpty()) {
            list($width, $height) = getimagesizefromstring($this->data);
            
            $this->width = $width;
            $this->height = $height;
        } else {
            $this->width = 0;
            $this->height = 0;
        }
    }

    private static $typesToExtensions = [
        'jpeg' => 'jpg',
        'png' => 'png',
        'gif' => 'gif',
    ];

    private static $extensionsToTypes = [
        'jpg' => 'jpeg',
        'jpeg' => 'jpeg',
        'png' => 'png',
        'gif' => 'gif',
    ];

    public static function getExtension(string $type) : ?string
    {
        return self::$typesToExtensions[$type] ?? null;
    }
    
    public static function getImageTypeFromPath(string $path) : string
    {
        $ext = File::getExtension($path);
        $type = self::$extensionsToTypes[$ext] ?? null;
        
        if (is_null($type)) {
            throw new InvalidConfigurationException(
                'No image type found for extension ' . $ext . '.'
            );
        }
        
        return $type;
    }
    
    public static function isValidImageType(string $type) : bool
    {
        return self::getExtension($type) !== null;
    }
    
    public static function isValidExtension(string $ext) : bool
    {
        return array_key_exists($ext, self::$extensionsToTypes);
    }
    
    public static function isImagePath(string $path) : bool
    {
        $ext = File::getExtension($path);
        return $ext && self::isValidExtension($ext);
    }

    /**
     * Build mime-type string
     * 'image/jpeg, image/png, image/gif'
     */
    public static function buildTypesString() : string
    {
        $parts = [];

        foreach (array_keys(self::$typesToExtensions) as $type) {
            $parts[] = 'image/' . $type;
        }

        return implode(', ', $parts);
    }

    public static function parseBase64(string $base64) : self
    {
        if (preg_match("#^data:image/(\w+);base64,(.*)$#i", $base64, $matches)) {
            $imgType = $matches[1];
            $data = $matches[2];

            if (strlen($data) > 0 && strlen($imgType) > 0) {
                return new static(base64_decode($data), $imgType);
            }
        }

        return static::makeEmpty();
    }
    
    /**
     * Creates Image from GD image (resource)
     */
    public static function fromGdImage($gdImage, $imgType = 'jpeg') : self
    {
        $data = self::gdImgToBase64($gdImage, $imgType);
        
        return new static($data, $imgType);
    }
    
    /**
     * Converts GD Image to Base64
     */
    private static function gdImgToBase64($gdImg, $format = 'jpeg') : ?string
    {
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
            } else {
                throw new InvalidArgumentException(
                    'Invalid image format: ' . $format
                );
            }
    
            $data = ob_get_contents();

            ob_end_clean();
        }
    
        return $data;
    }
    
    public static function load(string $fileName, string $imgType) : self
    {
        try {
            $data = File::load($fileName);
            return new static($data, $imgType);
        }
        catch (\Exception $ex) {
            throw new InvalidResultException(
                'Can\'t load image ' . $fileName . ': ' . $ex->getMessage()
            );
        }

        return self::makeEmpty();
    }

    public function notEmpty() : bool
    {
        return !$this->empty();
    }
    
    public function empty() : bool
    {
        return strlen($this->data) == 0;
    }
    
    public function isValid() : bool
    {
        return self::isValidImageType($this->imgType);
    }
    
    /**
     * @return void
     */
    public function save(string $fileName)
    {
        if ($this->empty()) {
            throw new InvalidOperationException('No data to save.');
        }

        File::save($fileName, $this->data);
    }
    
    /**
     * Returns GD image as a resource.
     */
    public function getGdImage()
    {
        if ($this->empty()) {
            return null;
        }
        
        return imagecreatefromstring($this->data);
    }
    
    public static function serializeRGBA(array $rgba) : string
    {
        return implode(',', array_values($rgba));
    }
    
    public static function deserializeRGBA(string $rgbaStr) : array
    {
        $rgba = explode(',', $rgbaStr);
        
        if (count($rgba) < 4) {
            throw new InvalidArgumentException('Invalid RGBA string: ' . $rgbaStr);
        }
        
        return [
            'red' => $rgba[0],
            'green' => $rgba[1],
            'blue' => $rgba[2],
            'alpha' => $rgba[3],
        ];
    }
    
    public function getAvgColor() : array
    {
        $image = $this->getGdImage();
        
        if (is_null($image)) {
            throw new InvalidArgumentException('No GD image found.');
        }
        
        $width = imagesx($image);
        $height = imagesy($image);
        
        $bgImage = imagecreatetruecolor(1, 1);
        imagecopyresampled($bgImage, $image, 0, 0, 0, 0, 1, 1, $width, $height);
        
        $rgb = imagecolorat($bgImage, 0, 0);
        $rgbArray = imagecolorsforindex($bgImage, $rgb);

        return $rgbArray;
    }
}
