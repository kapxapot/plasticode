<?php

namespace Plasticode\Util;

class Numbers
{
    private static $digits = [
        'm' => [ 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять' ],
        'f' => [ 'одна', 'две' ],
        'n' => [ 'одно' ]
    ];

    private static $tens = [
        'десять',
        'один$',
        'две$',
        'три$',
        'четыр$',
        'пят$',
        'шест$',
        'сем$',
        'восем$',
        'девят$',
    ];

    private static $decades = [
        'двадцать',
        'тридцать',
        'сорок',
        'пятьдесят',
        'шестьдесят',
        'семьдесят',
        'восемьдесят',
        'девяносто',
    ];

    private static $hundreds = [
        'сто',
        'двести',
        'триста',
        'четыреста',
        'пятьсот',
        'шестьсот',
        'семьсот',
        'восемьсот',
        'девятьсот',
    ];

    private static $lions = [
        'мил$',
        'миллиард',
        'трил$',
        'квадрил$',
        'квинтил$',
        'секстил$',
        'септил$',
        'октил$',
        'нонил$',
        'децил$',
    ];

    /**
     * Отрицательное число приводится к модулю. Дробная часть числа отбрасывается.
     */
    private static function normalize(float $num): int
    {
        if (!is_numeric($num)) {
            throw new \InvalidArgumentException("Number expected, got: {$num}.");
        }

        return floor(abs($num));
    }
    
    /**
     * Преобразует массив в число: [ 1, 2, 3, 4 ] => 1234.
     *
     * @param integer[] $a
     */
    public static function fromArray(array $a, bool $reverse = false): int
    {
        if ($reverse) {
            $a = array_reverse($a);
        }

        $n = 0;

        foreach ($a as $d) {
            $n = $n * 10 + $d;
        }

        return $n;
    }

    public static function toString(float $num): string
    {
        $num = self::normalize($num);

        if ($num == 0) {
            return 'ноль';
        }

        $result = '';
        $offset = 0;

        while ($num > 0) {
            if ($offset > 33) {
                throw new \OutOfRangeException('Oops, we can\'t count that far!');
            }
            
            $parts = [];
            
            $d321 = $num % 1000;
            $d21 = $d321 % 100;
            $d1 = $d21 % 10;

            $d2 = floor($d21 / 10);
            $d3 = floor($d321 / 100);

            if ($d3 > 0) {
                $parts[] = self::$hundreds[$d3 - 1];
            }
            
            if ($d2 == 1) {
                $parts[] = str_replace('$', 'надцать', self::$tens[$d21 - 10]);
            } else {
                if ($d2 >= 2) {
                    $parts[] = self::$decades[$d2 - 2];
                }

                if ($d1 > 0) {
                    $genderDigits = self::$digits[($offset == 3) ? 'f' : 'm'];
                    $parts[] = isset($genderDigits[$d1 - 1])
                        ? $genderDigits[$d1 - 1]
                        : self::$digits['m'][$d1 - 1];
                }
            }

            $appendix = null;

            if ($offset == 3) {
                $appendix = 'тысяч';
                if ($d2 != 1) {
                    if ($d1 == 1) {
                        $appendix .= 'а';
                    } elseif ($d1 >= 2 && $d1 <= 4) {
                        $appendix .= 'и';
                    }
                }
            } elseif ($offset > 3) {
                $end = 'ов';
                if ($d2 != 1) {
                    if ($d1 == 1) {
                        $end = '';
                    } elseif ($d1 >= 2 && $d1 <= 4) {
                        $end = 'а';
                    }
                }

                $appendix = str_replace('$', 'лион', self::$lions[($offset / 3) - 2]) . $end;
            }

            if ($appendix) {
                $parts[] = $appendix;
            }

            $result = implode(' ', $parts) . ((strlen($result) > 0) ? ' ' : '') . $result;

            $num = floor($num / 1000);
            $offset += 3;
        }

        return $result;
    }

    /**
     * Generates a random number.
     *
     * @param integer $digits Number of digits to generate [1..16].
     * @param boolean $zeroes Are zeroes allowed. Default = `false`.
     */
    public static function generate(int $digits, bool $zeroes = false): int
    {
        if ($digits < 1 || $digits > 16) {
            throw new \OutOfRangeException('Number of digits must be from 1 to 16.');
        }

        $a = [];

        $min = $zeroes ? 0 : 1;

        for ($i = 0; $i < $digits; $i++) {
            $a[] = mt_rand($min, 9);
        }

        return self::fromArray($a);
    }

    public static function parseFloat(string $str): float
    {
        return floatval(str_replace(',', '.', $str));
    }
}
