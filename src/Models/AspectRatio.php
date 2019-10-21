<?php

namespace Plasticode\Models;

class AspectRatio extends Model
{
    private $width;
    private $height;

    /**
     * Ratios must have bigger value first, smaller value second (to be >= 1)
     *
     * @var array
     */
    private $supportedRatios = [
        [ 1, 1 ],
        [ 2, 1 ],
        [ 3, 1 ],
        [ 3, 2 ],
    ];
    
    public function __construct(int $width, int $height, array $supportedRatios = null)
    {
        if ($width <= 0 || $height <= 0) {
            throw new \InvalidArgumentException('Width and height must be positive.');
        }
        
        $this->width = $width;
        $this->height = $height;
        
        if (!empty($supportedRatios)) {
            $this->setSupportedRatios($supportedRatios);
        }
    }

    private function setSupportedRatios(array $ratios)
    {
        // to do: validate input!
        $this->supportedRatios = $ratios;
    }

    private function isHorizontal() : bool
    {
        return $this->width >= $this->height;
    }
    
    private function isVertical() : bool
    {
        return !$this->isHorizontal();
    }
    
    /**
     * Exact ratio as a float value
     * 
     * Ratio is calculated as (bigger size / smaller size) and is always >= 1
     *
     * @return float
     */
    private function exact() : float
    {
        return $this->isHorizontal()
            ? $this->width / $this->height
            : $this->height / $this->width;
    }
    
    /**
     * Closest supported ratio as [x, y]
     *
     * @return array
     */
    private function closest() : array
    {
        $ratio = $this->exact();

        $minDelta = null;
        $minRatios = [];

        foreach ($this->supportedRatios as $sup) {
            $delta = abs($ratio - ($sup[0] / $sup[1]));
    
            if (is_null($minDelta) || $minDelta >= $delta) {
                if ($delta !== $minDelta) {
                    $minDelta = $delta;
                    $minRatios = [];
                }

                $minRatios[] = $sup;
            }
        }

        $result = null;

        // looking for the smallest ratio with the same delta
        foreach ($minRatios as $min) {
            if (is_null($result) || $min[0] < $result[0]) {
                $result = $min;
            }
        }
        
        return $result;
    }
    
    /**
     * Returns the maximum ratio (x / y)
     *
     * @return array
     */
    private function max() : array
    {
        $max = null;
        
        foreach ($this->supportedRatios as $sup) {
            if (is_null($max) || ($max[0] / $max[1] < $sup[0] / $sup[1])) {
                $max = $sup;
            }
        }
        
        return $max;
    }

    public function cssClasses() : string
    {
        $ratio = $this->closest();
        $max = $this->max();
        
        // what is this for?
        if ($ratio[0] / $ratio[1] > $max[0] / $max[1]) {
            $ratio = $max;
        }

        $hor = $this->isHorizontal();

        return
            'ratio-w' . ($hor ? $ratio[0] : $ratio[1]) . ' ' .
            'ratio-h' . ($hor ? $ratio[1] : $ratio[0]);
    }
}
