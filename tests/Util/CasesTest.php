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

    /**
     * @dataProvider caseForNumberAllCasesProvider
     */
    public function testCaseForNumberAllCases(
        string $word,
        int $num,
        int $case,
        string $expected
    ) : void
    {
        $cases = new Cases();

        $this->assertEquals(
            $expected,
            $cases->caseForNumber($word, $num, $case)
        );
    }

    public function caseForNumberAllCasesProvider() : array
    {
        return [
            ['карта', 1, Cases::NOM, 'карта'],
            ['карта', 2, Cases::NOM, 'карты'],
            ['карта', 5, Cases::NOM, 'карт'],
            ['карта', 1, Cases::GEN, 'карты'],
            ['карта', 2, Cases::GEN, 'карт'],
            ['карта', 5, Cases::GEN, 'карт'],
            ['карта', 1, Cases::DAT, 'карте'],
            ['карта', 2, Cases::DAT, 'картам'],
            ['карта', 5, Cases::DAT, 'картам'],
            ['карта', 1, Cases::ACC, 'карту'],
            ['карта', 2, Cases::ACC, 'карты'],
            ['карта', 5, Cases::ACC, 'карт'],
            ['карта', 1, Cases::ABL, 'картой'],
            ['карта', 2, Cases::ABL, 'картами'],
            ['карта', 5, Cases::ABL, 'картами'],
            ['карта', 1, Cases::PRE, 'о карте'],
            ['карта', 2, Cases::PRE, 'о картах'],
            ['карта', 5, Cases::PRE, 'о картах'],
        ];
    }
}
