<?php

namespace Plasticode\Util;

class Cases
{
	const NOM = 1; // именительный падеж
	const GEN = 2; // родительный падеж
	const DAT = 3; // дательный падеж
	const ACC = 4; // винительный падеж
	const ABL = 5; // творительный падеж
	const PRE = 6; // предложный падеж
	
	const SINGLE = 1; // единственное число
	const PLURAL = 2; // множественное число
	
	const MAS = 1; // мужской род
	const FEM = 2; // женский род
	const NEU = 3; // средний род
	const PLU = 4; // множественный род (ножницы, вилы)
	
	const INFINITIVE = 1; // инфинитив
	const PAST = 2; // прошлое время
	const PRESENT = 3; // настоящее время
	const FUTURE = 4; // будущее время
	
	const FIRST = 1; // первое лицо
	const SECOND = 2; // второе лицо
	const THIRD = 3; // третье лицо
	
	// [ [ имен ед, имен мн ], [ род ед, род мн ], [ дат ед, дат мн ], [ вин ед, вин мн ], [ твор ед, твор мн ], [ пред ед, пред мн ] ]
	private $caseTemplates = [
		// [картин]ка
		'картинка' => [ [ '%ка', '%ки' ], [ '%ки', '%ок' ], [ '%ке', '%кам' ], [ '%ку', '%ки' ], [ '%кой', '%ками' ], [ 'о %ке', 'о %ках' ] ],
		// [выпуск]
		'выпуск' => [ [ '%', '%и' ], [ '%а', '%ов' ], [ '%у', '%ам' ], [ '%', '%и' ], [ '%ом', '%ами' ], [ 'о %е', 'о %ах' ] ],
		// [стрим]
		'стрим' => [ [ '%', '%ы' ], [ '%а', '%ов' ], [ '%у', '%ам' ], [ '%', '%ы' ], [ '%ом', '%ами' ], [ 'о %е', 'о %ах' ] ],
		// [д]ень, [п]ень
		'день' => [ [ '%ень', '%ни' ], [ '%ня', '%ней' ], [ '%ню', '%ням' ], [ '%ень', '%ни' ], [ '%нём', '%нями' ], [ 'о %не', 'о %нях' ] ],
		// [пользовател]ь
		'пользователь' => [ [ '%ь', '%и' ], [ '%я', '%ей' ], [ '%ю', '%ям' ], [ '%я', '%ей' ], [ '%ем', '%ями' ], [ 'о %е', 'о %ях' ] ],
		// [минут]а
		'минута' => [ [ '%а', '%ы' ], [ '%ы', '%' ], [ '%е', '%ам' ], [ '%у', '%ы' ], [ '%ой', '%ами' ], [ 'о %е', 'о %ах' ] ],
		// [копи]я
		'копия' =>  [ [ '%я', '%и' ], [ '%и', '%й' ], [ '%и', '%ям' ], [ '%ю', '%и' ], [ '%ей', '%ями' ], [ 'о %и', 'о %ях' ] ],
	];
	
	// set 'index' if word != index
	// 'пень' => [ 'base' => 'п', 'index' => 'день' ],
	private $caseData = [
		'картинка' => [ 'base' => 'картин', 'gender' => self::FEM ],
		'выпуск' => [ 'base' => 'выпуск', 'gender' => self::MAS ],
		'стрим' => [ 'base' => 'стрим', 'gender' => self::MAS ],
		'день' => [ 'base' => 'д', 'gender' => self::MAS ],
		'пользователь' => [ 'base' => 'пользовател', 'gender' => self::MAS ],
		'час' => [ 'base' => 'час', 'index' => 'стрим', 'gender' => self::MAS ],
		'минута' => [ 'base' => 'минут', 'gender' => self::FEM ],
		'копия' => [ 'base' => 'копи', 'gender' => self::FEM ],
	];
	
	private $futureConjugationTemplates = [ [ 'буду', 'будем' ], [ 'будешь', 'будете' ], [ 'будет', 'будут' ] ];
	
	private $conjugationTemplates = [
		// [игра]ть
		'играть' => [
			self::INFINITIVE => '%ть',
			self::PAST => [ '%л', '%ла', '%ло', '%ли' ],
			self::PRESENT => [ [ '%ю', '%ем' ], [ '%ешь', '%ете' ], [ '%ет', '%ют' ] ]
		],
		// [ве]сти
		'вести' => [
			self::INFINITIVE => '%сти',
			self::PAST => [ '%л', '%ла', '%ло', '%ли' ],
			self::PRESENT => [ [ '%ду', '%дём' ], [ '%дёшь', '%дёте' ], [ '%дёт', '%дут' ] ]
		],
		// [транслир]овать
		'транслировать' => [
			self::INFINITIVE => '%овать',
			self::PAST => [ '%овал', '%овала', '%овало', '%овали' ],
			self::PRESENT => [ [ '%ую', '%уем' ], [ '%уешь', '%уете' ], [ '%ует', '%уют' ] ]
		],
	];
	
	// set 'index' if word != index
	// 'бравировать' => [ 'base' => 'бравир', 'index' => 'транслировать' ],
	private $conjugationData = [
		'играть' => [ 'base' => 'игра' ],
		'вести' => [ 'base' => 'ве' ],
		'транслировать' => [ 'base' => 'транслир' ],
	];
	
	/**
	 * Returns case data for word.
	 * 
	 * If word not found, throws exception.
	 * 
	 * @param string $word
	 */
	protected function getCaseData($word)
	{
		if (!array_key_exists($word, $this->caseData)) {
			throw new \InvalidArgumentException("Unknown word: {$word}.");
		}

        return $this->caseData[$word];
	}
	
	/**
	 * Returns conjugation data for word.
	 * 
	 * If word not found, throws exception.
	 * 
	 * @param string $word
	 */
	protected function getConjugationData($word)
	{
		if (!array_key_exists($word, $this->conjugationData)) {
			throw new \InvalidArgumentException("Unknown word: {$word}.");
		}

        return $this->conjugationData[$word];
	}
	
	/**
	 * Adds custom cases.
	 * 
	 * @param array $cases Custom cases settings.
	 * 
	 * Format:
	 * 
	 * 	[
	 *		'word' => '_word_',
	 *		'base' => '_base_',
	 *
	 * 		'gender' => Cases::MAS|Cases::FEM|Cases::NEU|Cases::PLU,
	 *		'forms' => [ [ '%', '%' ] x6 ],
	 * 
	 * 		OR
	 * 
	 * 		'index' => '_index_'
	 *	]
	 */
	public function addCases($cases = [])
	{
		$word = $cases['word'] ?? null;
		$base = $cases['base'] ?? null;
		$gender = $cases['gender'] ?? self::MAS;
		$forms = $cases['forms'] ?? null;
		$index = $cases['index'] ?? null;
		
		if (!$word || !$base || (!$forms && !$index)) {
			throw new \InvalidArgumentException("Invalid cases format.");
		}
		
		$data = [ 'base' => $base ];
		
		if ($index) {
			$data['index'] = $index;
		}

		$data['gender'] = $index
		    ? $this->gender($index)
		    : $gender;
		
		if ($forms) {
			$this->caseTemplates[$word] = $forms;
		}

		$this->caseData[$word] = $data;
	}

	/**
	 * Adds custom conjugations.
	 * 
	 * @param array $conjugations Custom conjugations settings.
	 * 
	 * Format:
	 * 
	 * 	[
	 * 		'word' => 'писать',
	 * 		'base' => 'пи',
	 *		
	 * 		'forms' => [
	 * 			Cases::INFINITIVE => '%',
	 * 			Cases::PAST => [ '%' x4 ],
	 * 			Cases::PRESENT => [ [ '%', '%' ] x3 ]
	 * 		]
	 * 
	 * 		OR
	 * 
	 * 		'index' => '_index_'
	 * 	]
	 */
	public function addConjugations($conjugations = [])
	{
		$word = $conjugations['word'] ?? null;
		$base = $conjugations['base'] ?? null;
		$forms = $conjugations['forms'] ?? null;
		$index = $conjugations['index'] ?? null;
		
		if (!$word || !$base || (!$forms && !$index)) {
			throw new \InvalidArgumentException("Invalid conjugations format.");
		}
		
		if ($forms) {
			$this->conjugationTemplates[$word] = $forms;
		}
		
		$data = [ 'base' => $base ];
		if ($index) {
			$data['index'] = $index;
		}

		$this->conjugationData[$word] = $data;
	}
	
	/**
	 * Определяет (единственное|множественное) число для натурального числа.
	 * 
	 * Например:
	 * 
	 * 1 ребенок - ед
	 * 2 ребенка - мн
	 * 11 ребят - мн
	 * 21 ребенок - ед
	 */
	public function numberForNumber($num)
	{
		return (($num % 10 == 1) && ($num % 100 != 11))
			? self::SINGLE
			: self::PLURAL;
	}
	
	public function gender($word)
	{
	    $data = $this->getCaseData($word);
		return $data['gender'] ?? Cases::MAS;
	}
	
	/**
	 * Возвращает форму существительного, соответствующую указанному натуральному числу.
	 */
	public function caseForNumber($word, $num)
	{
		if ($num < 0) {
			throw new \InvalidArgumentException('Number must be non-negative.');
		}

		$data = $this->getCaseData($word);

		$case = self::GEN;
		$caseNumber = self::PLURAL;
		
		// только 2 последние цифры влияют на форму существительного
		$num = $num % 100;
		
		if ($num < 5 || $num > 20) {
			switch ($num % 10) {
				case 1:
					$case = self::NOM;
					$caseNumber = self::SINGLE;
					break;

				case 2:
				case 3:
				case 4:
					$case = self::GEN;
					$caseNumber = self::SINGLE;
					break;
			}
		}
		
		$templateIndex = $data['index'] ?? $word;
		$base = $data['base'];

		if (!array_key_exists($templateIndex, $this->caseTemplates)) {
			throw new \InvalidArgumentException("No cases template for index: {$templateIndex}.");
		}

		$templateData = $this->caseTemplates[$templateIndex];
		$template = $templateData[$case - 1][$caseNumber - 1] ?? '%';

		return str_replace('%', $base, $template);
	}
	
	/**
	 * [1..4] time, [1..3] person, [sp] number, [mfnp] gender
	 */
	private function parseConjugationForm($str)
	{
		$bits = str_split($str);
		
		if (count($bits) < 3) {
			throw new \InvalidArgumentException("Incorrect conjugation form format: {$str}.");
		}
		
		$numbers = [
			's' => self::SINGLE,
			'p' => self::PLURAL
		];
		
		$genders = [
			'm' => self::MAS,
			'f' => self::FEM,
			'n' => self::NEU,
			'p' => self::PLU
		];
		
		$form = [
			'time' => (int)$bits[0],
			'person' => (int)$bits[1],
			'number' => is_numeric($bits[2]) ? $bits[2] : $numbers[$bits[2]]
		];
		
		if (count($bits) == 4) {
			$form['gender'] = is_numeric($bits[3]) ? $bits[3] : $genders[$bits[3]];
		}
		
		return $form;
	}
	
	/**
	 * Returns conjugation for word based on form.
	 * 
	 * @param string $word
	 * @param mixed $form Array OR string.
	 */
	public function conjugation($word, $form)
	{
		$data = $this->getConjugationData($word);
		$base = $data['base'];
		
		$templateIndex = $data['index'] ?? $word;

		if (!array_key_exists($templateIndex, $this->conjugationTemplates)) {
			throw new \InvalidArgumentException("No conjugations template for index: {$templateIndex}.");
		}
		
		$templateData = $this->conjugationTemplates[$templateIndex];

		if (!is_array($form)) {
			$form = $this->parseConjugationForm($form);
		}

		$time = $form['time'];
		$person = $form['person'];
		$number = $form['number'];
		$gender = $form['gender'];
		
		if ($number == self::PLURAL) {
			$gender = self::PLU;
		}
		
		if ($gender == null) {
			throw new \InvalidArgumentException("Undefined gender.");
		}

		if ($time == self::INFINITIVE) {
			$template = $templateData[self::INFINITIVE];
		}
		elseif ($time == self::PAST) {
			$template = $templateData[self::PAST][$gender - 1];
		}
		elseif ($time == self::PRESENT) {
			$template = $templateData[self::PRESENT][$person - 1][$number - 1];
		}
		elseif ($time == self::FUTURE) {
			if (array_key_exists(self::FUTURE, $templateData)) {
				$template = $templateData[self::FUTURE][$person - 1][$number - 1];
			}
			else {
				$template = $this->futureConjugationTemplates[$person - 1][$number - 1] . ' ' . $templateData[self::INFINITIVE];
			}
		}

		if ($template) {
			$result = str_replace('%', $base, $template);
		}

		if (!$result) {
			$error = "No conjugation found for word: {$word}, time: {$time}, person: {$person}, number: {$number}, gender: {$gender}";
		}
		
		return $result ?? $error ?? $word;
	}
}
