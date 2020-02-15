<?php

namespace Plasticode\Tests\Util;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\SortStep;

final class SortStepTest extends TestCase
{
    public function testDefault() : void
    {
        $field = 'field';
        $step = new SortStep($field);

        $this->assertEquals($field, $step->getField());
        $this->assertEquals(false, $step->isDesc());
    }

    /**
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
