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
        //[ 4, 1 ],
        //[ 5, 1 ],
        [ 3, 2 ],
        //[ 4, 3 ],
        //[ 5, 2 ],
        //[ 5, 3 ],
    ];
    
    /**
     * Creates AspectRatio object.
     */
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
    
    public function ratioExact() : float
    {
        return $this->isHorizontal()
            ? $this->width / $this->height
            : $this->height / $this->width;
    }
    
    public function ratioApprox() : array
    {
        $ratio = $this->ratioExact();

        $minDelta = null;
        $minRatios = [];

        foreach ($this->supportedRatios as $supRatio) {
            $delta = abs($ratio - ($supRatio[0] / $supRatio[1]));
    
            if ($minDelta === null || $minDelta >= $delta) {
                if ($delta !== $minDelta) {
                    $minDelta = $delta;
                    $minRatios = [];
                }

                $minRatios[] = $supRatio;
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
        $maxRatio = null;
        
        foreach ($this->supportedRatios as $supRatio) {
            if ($maxRatio === null || ($maxRatio[0] / $maxRatio[1]) < ($supRatio[0] / $supRatio[1])) {
                $maxRatio = $supRatio;
            }
        }
        
        return $maxRatio;
    }

    public function cssClasses()
    {
        $ratio = $this->ratioApprox();
        $maxRatio = $this->maxRatio();
        
        if ($ratio[0] / $ratio[1] > $maxRatio[0] / $maxRatio[1]) {
            $ratio = $maxRatio;
        }

        $hor = $this->isHorizontal();

        return 'ratio-w' . ($hor ? $ratio[0] : $ratio[1]) . ' ratio-h' . ($hor ? $ratio[1] : $ratio[0]);
    }
}
