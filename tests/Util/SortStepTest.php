<?php

namespace Plasticode\Tests\Util;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\SortStep;

final class SortStepTest extends TestCase
{
    public function testFieldImplicitAsc() : void
    {
        $field = 'field';
        $step = SortStep::create($field);

        $this->assertEquals($field, $step->getField());
        $this->assertEquals(false, $step->isDesc());
    }

    public function testFieldImplicitDesc() : void
    {
        $field = 'field';
        $step = SortStep::createDesc($field);

        $this->assertEquals($field, $step->getField());
        $this->assertEquals(true, $step->isDesc());
    }

    /**
     * @dataProvider explicitSortDirectionProvider
     */
    public function testExplicitSortDirection(string $field, bool $desc) : void
    {
        $step = new SortStep($field, null, $desc);

        $this->assertEquals($field, $step->getField());
        $this->assertEquals($desc, $step->isDesc());
    }

    public function explicitSortDirectionProvider() : array
    {
        return [
            ['field', false],
            ['field', true],
        ];
    }
}
