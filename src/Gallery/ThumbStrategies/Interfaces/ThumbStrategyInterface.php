<?php

namespace Plasticode\Gallery\ThumbStrategies\Interfaces;

use ORM;
use Plasticode\Gallery\Gallery;
use Plasticode\IO\Image;

interface ThumbStrategyInterface
{
    /**
     * Get thumb from save data (API call).
     */
    function getThumb(Gallery $gallery, ORM $item, array $data) : ?Image;

    /**
     * Creates GD image for thumb.
     *
     * @param resource $image
     * @return resource
     */
    function createThumb($image);
}
