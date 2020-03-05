<?php

namespace Plasticode\Tests\Parsing\LinkMappers;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Mocks\LinkMappers\TaggedLinkMapperMock;

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
