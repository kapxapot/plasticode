<?php

namespace Plasticode\Parsing;

use InvalidArgumentException;
use Plasticode\Collections\ContentsItemCollection;
use Webmozart\Assert\Assert;

/**
 * Context for TitlesStep parsing.
 * 
 * Accumulates contents items and tracks the paragraph count.
 */
class TitlesContext
{
    private int $minLevel;
    private int $maxLevel;
    private ContentsItemCollection $contents;

    /** @var integer[] */
    private array $count = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(int $minLevel, int $maxLevel)
    {
        Assert::greaterThan($minLevel, 0);
        Assert::greaterThanEq($maxLevel, $minLevel);

        $this->minLevel = $minLevel;
        $this->maxLevel = $maxLevel;

        $this->contents = ContentsItemCollection::make();

        $this->zeroCount($this->minLevel);
    }

    /**
     * Increments count for $level and zeroes all next counts.
     * 
     * @throws InvalidArgumentException
     */
    public function incCount(int $level) : void
    {
        $this->checkLevel($level);

        $this->count[$level]++;
        $this->zeroCount($level + 1);
    }

    /**
     * Fills count array with zeroes starting from $startLevel.
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
     * @return integer[]
     * 
     * @throws InvalidArgumentException
     */
    public function getCountSlice(int $level) : array
    {
        $this->checkLevel($level);

        return array_slice($this->count, 0, $level - $this->minLevel + 1);
    }

    /**
     * Checks that $level is in [$this->minLevel, $this->maxLevel] range.
     * 
     * @throws InvalidArgumentException
     */
    private function checkLevel(int $level) : void
    {
        Assert::greaterThanEq($level, $this->minLevel);
        Assert::lessThanEq($level, $this->maxLevel);
    }

    /**
     * Adds item to contents.
     */
    public function addContents(ContentsItem $item) : void
    {
        $this->contents = $this->contents->add($item);
    }

    public function getContents() : ContentsItemCollection
    {
        return $this->contents;
    }
}
