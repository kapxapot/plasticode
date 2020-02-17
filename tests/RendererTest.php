<?php

namespace Plasticode\Tests;

use Plasticode\Parsing\ContentsItem;
use Plasticode\ViewModels\ContentsViewModel;
use Plasticode\ViewModels\QuoteViewModel;

final class RendererTest extends BaseRenderTestCase
{
    public function testPrev() : void
    {
        $this->assertEquals(
            '<i class="glyphicon glyphicon-chevron-left" aria-hidden="true"></i>',
            $this->renderer->prev()
        );
    }

    public function testNext() : void
    {
        $this->assertEquals(
            '<i class="glyphicon glyphicon-chevron-right" aria-hidden="true"></i>',
            $this->renderer->next()
        );
    }

    public function testContents() : void
    {
        $contents = new ContentsViewModel(
            [
                new ContentsItem(1, '1', 'Title'),
                new ContentsItem(3, '1_2_3', 'Some <a href="ababa">text</a>'),
            ]
        );

        $this->assertEquals(
            '<div class="panel-body contents"><div class="contents--header">Содержание:</div><div class="contents--body mt-2"><div class="contents--level1"><a href="#1">1. Title</a></div><div class="contents--level3"><a href="#1_2_3">1.2.3. Some text</a></div></div></div>',
            $this->renderer->component('contents', $contents)
        );
    }

    public function testQuoteExtended() : void
    {
        $model = new QuoteViewModel('quote text', 'Blizzard', null, [], 'some-style');

        $this->assertEquals(
            '<div class="quote some-style"><div class="quote--header"><span class="quote--author">Blizzard</span>:</div><div class="quote--body">quote text</div></div>',
            $this->renderer->component('quote', $model)
        );
    }
}
