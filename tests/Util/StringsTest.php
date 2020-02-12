<?php

namespace Plasticode\Tests\Util;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Strings;

/**
 * @covers \Plasticode\Util\Strings
 */
final class StringsTest extends TestCase
{
    /**
     * @dataProvider alphaNumProvider
     */
    public function testToAlphaNum(?string $original, ?string $expected) : void
    {
        $this->assertEquals(
            $expected,
            Strings::toAlphaNum($original)
        );
    }

    public function alphaNumProvider()
    {
        return [
            [null, null],
            ['abc_123', 'abc_123'],
            ['aba ba, hey', 'ababahey'],
            ['русский yazyk!', 'русскийyazyk'],
        ];
    }

    /**
     * @dataProvider hashTagsProvider
     */
    public function testHashTags(array $original, string $expected) : void
    {
        $this->assertEquals(
            $expected,
            Strings::hashTags($original)
        );
    }

    public function hashTagsProvider()
    {
        return [
            [['abc', 'def ghi'], '#abc #defghi'],
            [['abc!!', '#def_1-2-3'], '#abc #def_123'],
        ];
    }

    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize(?string $original, ?string $expected) : void
    {
        $this->assertEquals(
            $expected,
            Strings::normalize($original)
        );
    }

    public function normalizeProvider()
    {
        return [
            [null, null],
            ['', ''],
            [' aza ZA    uhuhuh ', 'aza za uhuhuh'],
        ];
    }
}
