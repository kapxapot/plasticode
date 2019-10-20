<?php

namespace Plasticode\Models;

class AspectRatio extends Model
{
    private $width;
    private $height;

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
        
        if (is_array($supportedRatios)) {
            $this->supportedRatios = $supportedRatios;
        }
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

        $resultRatio = null;

        foreach ($minRatios as $minRatio) {
            if ($resultRatio === null || $minRatio[0] < $resultRatio[0]) {
                $resultRatio = $minRatio;
            }
        }
        
        return $resultRatio;
    }
    
    private function maxRatio() : array
    {
        $max = null;
        
        foreach ($this->supportedRatios as $sup) {
            if (is_null($max) || ($max[0] / $max[1]) < ($sup[0] / $sup[1])) {
                $max = $sup;
            }
        }
        
        return $max;
    }

    public function cssClasses() : string
    {
        $ratio = $this->closest();
        $max = $this->maxRatio();
        
        if ($ratio[0] / $ratio[1] > $max[0] / $max[1]) {
            $ratio = $max;
        }

        $hor = $this->isHorizontal();

        return 'ratio-w' . ($hor ? $ratio[0] : $ratio[1]) . ' ratio-h' . ($hor ? $ratio[1] : $ratio[0]);
    }
}
