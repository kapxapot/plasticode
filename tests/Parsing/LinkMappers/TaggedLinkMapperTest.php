<?php

namespace Plasticode\Tests\Parsing\LinkMappers;

use PHPUnit\Framework\TestCase;
use Plasticode\Tests\Mocks\TaggedLinkMapperMock;

final class TaggedLinkMapperTest extends TestCase
{
    public function testParse() : void
    {
        $mapper = new TaggedLinkMapperMock();

        $this->assertEquals(
            'mock: <123>hello</123>',
            $mapper->map(['mock:123', 'hello'])
        );
    }
}
