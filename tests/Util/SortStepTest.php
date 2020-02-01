<?php

namespace Plasticode\Tests\Util;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\SortStep;

final class SortStepTest extends TestCase
{
    /**
     * @covers SortStep
     */
    public function testDefault() : void
    {
        $field = 'field';
        $step = new SortStep($field);

        $this->assertEquals($field, $step->getField());
        $this->assertEquals(false, $step->isDesc());
    }

    /**
     * @covers SortStep
     * @dataProvider explicitProvider
     */
    public function testExplicitAsc(string $field, bool $desc) : void
    {
        $step = new SortStep($field, $desc);

        $this->assertEquals($field, $step->getField());
        $this->assertEquals($desc, $step->isDesc());
    }

    public function explicitProvider() : array
    {
        return [
            ['field', false],
            ['field', true],
        ];
    }
}
