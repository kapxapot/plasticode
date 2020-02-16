<?php

namespace Plasticode\Tests;

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
}
