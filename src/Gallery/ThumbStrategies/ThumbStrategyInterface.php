<?php

namespace Plasticode\Gallery\ThumbStrategies;

use Plasticode\Gallery\Gallery;
use Plasticode\IO\Image;

interface ThumbStrategyInterface
{
    /**
     * Get thumb from save data (API call)
     * 
     * @return Image|null
     */
    public function getThumb(Gallery $gallery, \ORM $item, array $data) : ?Image;

    /**
     * Creates GD image for thumb
     *
     * @param resource $image
     * @return resource
     */
    public function createThumb($image);
}
