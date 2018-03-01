<?php

namespace Plasticode\Util;

class Numbers {
	private $digits = [
		'm' => [ 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять' ],
		'f' => [ 'одна', 'две' ],
		'n' => [ 'одно' ]
	];
	
	private $tens = [
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
	
	private $decades = [
		'двадцать',
		'тридцать',
		'сорок',
		'пятьдесят',
		'шестьдесят',
		'семьдесят',
		'восемьдесят',
		'девяносто',
	];
	
	private $hundreds = [
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
	
	private $lions = [
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
	private function normalize($num) {
		if (!is_numeric($num)) {
			throw new \InvalidArgumentException("Number expected, got this: {$num}.");
		}

		return floor(abs($num));
	}
	
	/**
	 * Преобразует массив в число. [ 1, 2, 3, 4 ] => 1234.
	 */
	public function fromArray($a, $reverse = false) {
		if ($reverse) {
			$a = array_reverse($a);
		}
		
		$n = 0;
		
		foreach ($a as $d) {
			$n = $n * 10 + $d;
		}
		
		return $n;
	}

	/**
	 * Преобразует число в массив. 1234 => [ 4, 3, 2, 1 ].
	 */
	private function toArray($num, $reverse = false) {
		$num = $this->normalize($num);

		$a = [];
		
		while ($num > 0) {
			$a[] = $num % 10;
			$num = floor($num / 10);
		}

		return $reverse ? $a : array_reverse($a);
	}
	
	private function chopTriad(&$num) {
		if (is_array($num)) {
			$triad = 0;
			
			for ($i = 0; $i < 3; $i++) {
				if (empty($num)) {
					break;
				}

				$triad = $triad * 10 + array_shift($num);
			}
		}
		else {
			$triad = $num % 1000;
			$num = floor($num / 1000);
		}
		
		return $triad;
	}

	public function toString($num) {
		$num = $this->normalize($num);

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
				$parts[] = $this->hundreds[$d3 - 1];
			}
			
			if ($d2 == 1) {
				$parts[] = str_replace('$', 'надцать', $this->tens[$d21 - 10]);
			}
			else {
				if ($d2 >= 2) {
					$parts[] = $this->decades[$d2 - 2];
				}
	
				if ($d1 > 0) {
					$genderDigits = $this->digits[($offset == 3) ? 'f' : 'm'];
					$parts[] = isset($genderDigits[$d1 - 1])
						? $genderDigits[$d1 - 1]
						: $this->digits['m'][$d1 - 1];
				}
			}

			if ($offset == 3) {
				$appendix = 'тысяч';
				if ($d2 != 1) {
					if ($d1 == 1) {
						$appendix .= 'а';
					}
					elseif ($d1 >= 2 && $d1 <= 4) {
						$appendix .= 'и';
					}
				}
			}
			elseif ($offset > 3) {
				$end = 'ов';
				if ($d2 != 1) {
					if ($d1 == 1) {
						$end = '';
					}
					elseif ($d1 >= 2 && $d1 <= 4) {
						$end = 'а';
					}
				}

				$appendix = str_replace('$', 'лион', $this->lions[($offset / 3) - 2]) . $end;
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
	 * Generates random number.
	 * 
	 * @param int $digits Number of digits to generate. [1..]
	 * @param bool $zeroes Are zeroes allowed. Default = false.
	 * 
	 * @return int
	 */
	public function generate($digits, $zeroes = false) {
		if ($digits < 1 || $digits > 16) {
			throw new \OutOfRangeException('Number of digits must be from 1 to 16.');
		}

		$a = [];
		
		$min = $zeroes ? 0 : 1;
		
		for ($i = 0; $i < $digits; $i++) {
			$a[] = mt_rand($min, 9);
		}
		
		return $this->fromArray($a);
	}
}
