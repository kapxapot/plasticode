<?php

namespace Plasticode\Parsing;

use Plasticode\Collection;
use Webmozart\Assert\Assert;

/**
 * Context for TitlesStep parsing.
 */
class TitlesContext
{
    /** @var integer */
    private $minLevel;

    /** @var integer */
    private $maxLevel;

    /** @var \Plasticode\Collection */
    private $contents;

    /** @var integer[] */
    private $count = [];

    public function __construct(int $minLevel, int $maxLevel)
    {
        $this->minLevel = $minLevel;
        $this->maxLevel = $maxLevel;

        $this->contents = Collection::makeEmpty();

        $this->zeroCount($this->minLevel);
    }

    /**
     * Increments count for $level and zeroes all next counts.
     *
     * @param integer $level
     * @return void
     */
    public function incCount(int $level) : void
    {
        $this->checkLevel($level);

        $this->count[$level]++;
        $this->zeroCount($level + 1);
    }

    /**
     * Fills count array with zeroes starting from $startLevel.
     *
     * @param integer $startLevel
     * @return void
     */
    private function zeroCount(int $startLevel) : void
    {
        for ($i = $startLevel; $i <= $this->maxLevel; $i++) {
            $this->count[$i] = 0;
        }
    }

    /**
     * Returns count slice from start to $level.
     *
     * @param integer $level
     * @return array
     */
    public function getCountSlice(int $level) : array
    {
        $this->checkLevel($level);

        return array_slice($this->count, 0, $level - $this->minLevel + 1);
    }

    /**
     * Checks that $level is in [$this->minLevel, $this->maxLevel] range.
     *
     * @param integer $level
     * @return void
     */
    private function checkLevel(int $level) : void
    {
        Assert::greaterThanEq($level, $this->minLevel);
        Assert::lessThanEq($level, $this->maxLevel);
    }

    /**
     * Adds item to contents.
     *
     * @param ContentsItem $item
     * @return void
     */
    public function addContents(ContentsItem $item) : void
    {
        $this->contents = $this->contents->add($item);
    }

    public function getContents() : Collection
    {
        return $this->contents;
    }
}
