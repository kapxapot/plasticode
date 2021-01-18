<?php

namespace Plasticode\Models;

use Webmozart\Assert\Assert;

class AspectRatio
{
    private int $width;
    private int $height;

    /**
     * Ratios must have bigger value first, smaller value second (to be >= 1).
     *
     * @var integer[][]
     */
    private $supportedRatios = [
        [1, 1],
        [2, 1],
        [3, 1],
        [3, 2],
    ];

    /**
     * @param integer[][]|null $supportedRatios
     */
    public function __construct(int $width, int $height, ?array $supportedRatios = null)
    {
        Assert::greaterThan($width, 0);
        Assert::greaterThan($height, 0);

        $this->width = $width;
        $this->height = $height;

        if (!empty($supportedRatios)) {
            $this->validateRatios($supportedRatios);
            $this->setSupportedRatios($supportedRatios);
        }
    }

    /**
     * Validates ratios.
     *
     * @param integer[][] $ratios
     */
    private function validateRatios(array $ratios): void
    {
        foreach ($ratios as $ratio) {
            Assert::isArray($ratio);
            Assert::count($ratio, 2);
            Assert::allNatural($ratio);
        }
    }

    /**
     * Sets new supported ratios assuming that they are valid.
     * 
     * @param integer[][] $ratios
     */
    private function setSupportedRatios(array $ratios): void
    {
        /** @var integer[][] */
        $this->supportedRatios = [];

        foreach ($ratios as $ratio) {
            $x = $ratio[0];
            $y = $ratio[1];

            $this->supportedRatios[] = $x >= $y
                ? [$x, $y]
                : [$y, $x];
        }
    }

    /**
     * Returns true, if width >= height.
     */
    public function isHorizontal(): bool
    {
        return $this->width >= $this->height;
    }

    /**
     * Returns true, if width < height.
     */
    public function isVertical(): bool
    {
        return !$this->isHorizontal();
    }

    /**
     * Returns the exact ratio as a float value.
     * 
     * Ratio is calculated as (bigger size / smaller size) and is always >= 1.
     */
    public function exact(): float
    {
        return $this->isHorizontal()
            ? $this->width / $this->height
            : $this->height / $this->width;
    }

    /**
     * Returns the closest supported ratio as [x, y].
     *
     * @return integer[]
     */
    public function closest(): array
    {
        $ratio = $this->exact();

        /** @var float|null */
        $minDelta = null;

        /** @var integer[][] */
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

        /** @var integer[]|null */
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
     * Generates CSS classes for the closest ratio.
     * Returns empty string if there is none.
     */
    public function cssClasses(): string
    {
        $ratio = $this->closest();
        $hor = $this->isHorizontal();

        return
            'ratio-w' . ($hor ? $ratio[0] : $ratio[1]) . ' ' .
            'ratio-h' . ($hor ? $ratio[1] : $ratio[0]);
    }
}
