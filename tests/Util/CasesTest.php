<?php

namespace Plasticode\Tests\Util;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Cases;

final class CasesTest extends TestCase
{
    /**
     * @dataProvider caseForNumberProvider
     */
    public function testCaseForNumber(string $word, int $num, string $expected) : void
    {
        $cases = new Cases();

        $this->assertEquals(
            $expected,
            $cases->caseForNumber($word, $num)
        );
    }

    public function caseForNumberProvider() : array
    {
        return [
            ['картинка', 10, 'картинок'],
            ['выпуск', 15, 'выпусков'],
            ['стрим', 51, 'стрим'],
            ['день', 132, 'дня'],
            ['пользователь', 5, 'пользователей'],
            ['час', 11, 'часов'],
            ['минута', 4, 'минуты'],
            ['копия', 8, 'копий'],
            ['зритель', 2, 'зрителя'],
            ['слово', 6, 'слов'],
            ['ассоциация', 7, 'ассоциаций'],
            ['ход', 2038, 'ходов'],
            ['карта', 55, 'карт'],
        ];
    }
}
