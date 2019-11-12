<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Strings;

final class StringsTest extends TestCase
{
    /** @dataProvider alphaNumProvider */
    public function testToAlphaNum(?string $original, ?string $expected): void
    {
        $this->assertEquals(
            Strings::toAlphaNum($original),
            $expected
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
}
