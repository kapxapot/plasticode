<?php

namespace Plasticode\Tests\Parsing;

use PHPUnit\Framework\TestCase;
use Plasticode\Parsing\ContentsItem;

final class ContentsItemTest extends TestCase
{
    public function testContentsItem() : void
    {
        $item1 = new ContentsItem(3, '1_2_3', 'Some <a href="ababa">text</a>');

        $this->assertEquals(3, $item1->level());
        $this->assertEquals('1_2_3', $item1->label());
        $this->assertEquals('Some <a href="ababa">text</a>', $item1->text());
        $this->assertEquals('1.2.3. Some text', $item1->displayText());

        $item2 = new ContentsItem(1, null, 'Title');

        $this->assertEquals(1, $item2->level());
        $this->assertEquals(null, $item2->label());
        $this->assertEquals('Title', $item2->text());
        $this->assertEquals('Title', $item2->displayText());
    }
}
