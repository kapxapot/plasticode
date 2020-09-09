<?php

namespace Plasticode\Util;

use Webmozart\Assert\Assert;

/**
 * Cases & conjugations support for Russian language.
 * 
 * Падежи и склонения для русского языка.
 */
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

    // [[им ед, им мн], [род ед, род мн], [дат ед, дат мн], [вин ед, вин мн], [тв ед, тв мн], [пр ед, пр мн ]]
    private array $caseTemplates = [
        // [картин]ка
        'картинка' => [['%ка', '%ки'], ['%ки', '%ок'], ['%ке', '%кам'], ['%ку', '%ки'], ['%кой', '%ками'], ['о %ке', 'о %ках']],
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
        // [слов]о
        'слово' => [['%о', '%а'], ['%а', '%'], ['%у', '%ам'], ['%о', '%а'], ['%ом', '%ами'], ['о %е', 'о %ах']],
    ];

    // set 'index' if word != index
    // 'пень' => [ 'base' => 'п', 'index' => 'день' ],
    private array $caseData = [
        'картинка' => ['base' => 'картин', 'gender' => self::FEM],
        'выпуск' => ['base' => 'выпуск', 'gender' => self::MAS],
        'стрим' => ['base' => 'стрим', 'gender' => self::MAS],
        'день' => ['base' => 'д', 'gender' => self::MAS],
        'пользователь' => ['base' => 'пользовател', 'gender' => self::MAS],
        'час' => ['base' => 'час', 'index' => 'стрим', 'gender' => self::MAS],
        'минута' => ['base' => 'минут', 'gender' => self::FEM],
        'копия' => ['base' => 'копи', 'gender' => self::FEM],
        'зритель' => ['base' => 'зрител', 'index' => 'пользователь', 'gender' => self::MAS],
        'слово' => ['base' => 'слов', 'gender' => self::NEU],
        'ассоциация' => ['base' => 'ассоциаци', 'index' => 'копия', 'gender' => self::FEM],
        'ход' => ['base' => 'ход', 'index' => 'стрим', 'gender' => self::MAS],
        'карта' => ['base' => 'карт', 'index' => 'минут', 'gender' => self::FEM],
    ];

    private array $futureConjugationTemplates = [
        ['буду', 'будем'],
        ['будешь', 'будете'],
        ['будет', 'будут']
    ];

    private array $conjugationTemplates = [
        // [игра]ть
        'играть' => [
            self::INFINITIVE => '%ть',
            self::PAST => ['%л', '%ла', '%ло', '%ли'],
            self::PRESENT => [['%ю', '%ем'], ['%ешь', '%ете'], ['%ет', '%ют']]
        ],
        // [ве]сти
        'вести' => [
            self::INFINITIVE => '%сти',
            self::PAST => ['%л', '%ла', '%ло', '%ли'],
            self::PRESENT => [['%ду', '%дём'], ['%дёшь', '%дёте'], ['%дёт', '%дут']]
        ],
        // [транслир]овать
        'транслировать' => [
            self::INFINITIVE => '%овать',
            self::PAST => ['%овал', '%овала', '%овало', '%овали'],
            self::PRESENT => [['%ую', '%уем'], ['%уешь', '%уете'], ['%ует', '%уют']]
        ],
    ];

    // set 'index' if word != index
    // 'бравировать' => [ 'base' => 'бравир', 'index' => 'транслировать' ],
    private array $conjugationData = [
        'играть' => ['base' => 'игра'],
        'вести' => ['base' => 'ве'],
        'транслировать' => ['base' => 'транслир'],
    ];

    /**
     * Returns case data for a word.
     * 
     * If the word not found, throws {@see \InvalidArgumentException}.
     * 
     * @throws \InvalidArgumentException
     */
    protected function getCaseData(string $word) : array
    {
        Assert::keyExists(
            $this->caseData,
            $word,
            'Unknown word: ' . $word . '.'
        );

        return $this->caseData[$word];
    }

    /**
     * Returns conjugation data for a word.
     * 
     * If the word is not found, throws {@see \InvalidArgumentException}.
     * 
     * @throws \InvalidArgumentException
     */
    protected function getConjugationData(string $word) : array
    {
        Assert::keyExists(
            $this->conjugationData,
            $word,
            'Unknown word: ' . $word . '.'
        );

        return $this->conjugationData[$word];
    }

    /**
     * Adds custom cases.
     * 
     * @param array $cases Custom cases settings.
     * @throws \InvalidArgumentException
     * 
     * Format:
     * 
     * [
     *     'word' => '_word_',
     *     'base' => '_base_',
     * 
     *     'gender' => Cases::MAS|Cases::FEM|Cases::NEU|Cases::PLU,
     *     'forms' => [ [ '%', '%' ] x6 ],
     * 
     *     OR
     * 
     *     'index' => '_index_'
     * ]
     */
    public function addCases(array $cases = []) : void
    {
        $word = $cases['word'] ?? null;
        $base = $cases['base'] ?? null;
        $gender = $cases['gender'] ?? self::MAS;
        $forms = $cases['forms'] ?? null;
        $index = $cases['index'] ?? null;

        Assert::true(
            $word && $base && ($forms || $index),
            'Invalid cases format.'
        );

        $data = ['base' => $base];

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
     * @throws \InvalidArgumentException
     * 
     * Format:
     * 
     *  [
     *      'word' => 'писать',
     *      'base' => 'пи',
     *      
     *      'forms' => [
     *          Cases::INFINITIVE => '%',
     *          Cases::PAST => [ '%' x4 ],
     *          Cases::PRESENT => [ [ '%', '%' ] x3 ]
     *      ]
     * 
     *      OR
     * 
     *      'index' => '_index_'
     *  ]
     */
    public function addConjugations(array $conjugations = []) : void
    {
        $word = $conjugations['word'] ?? null;
        $base = $conjugations['base'] ?? null;
        $forms = $conjugations['forms'] ?? null;
        $index = $conjugations['index'] ?? null;

        Assert::true(
            $word && $base && ($forms || $index),
            'Invalid conjugations format.'
        );

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
    public function numberForNumber(int $num) : int
    {
        return (($num % 10 == 1) && ($num % 100 != 11))
            ? self::SINGLE
            : self::PLURAL;
    }

    public function gender(string $word) : int
    {
        $data = $this->getCaseData($word);
        return $data['gender'] ?? Cases::MAS;
    }

    /**
     * Возвращает форму существительного, соответствующую указанному натуральному числу.
     * 
     * @throws \InvalidArgumentException
     */
    public function caseForNumber(string $word, int $num) : string
    {
        Assert::greaterThanEq(
            $num,
            0,
            'Number must be non-negative.'
        );

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

        Assert::keyExists(
            $this->caseTemplates,
            $templateIndex,
            'No cases template for index: ' . $templateIndex . '.'
        );

        $templateData = $this->caseTemplates[$templateIndex];
        $template = $templateData[$case - 1][$caseNumber - 1] ?? '%';

        return str_replace('%', $base, $template);
    }

    /**
     * [1..4] time, [1..3] person, [sp] number, [mfnp] gender.
     * 
     * @throws \InvalidArgumentException
     */
    private function parseConjugationForm(string $str) : array
    {
        $bits = str_split($str);

        Assert::minCount(
            $bits,
            3,
            'Incorrect conjugation form format: ' . $str . '.'
        );

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
     * @param array|string $form Array OR string.
     * @throws \InvalidArgumentException
     */
    public function conjugation(string $word, $form) : string
    {
        $data = $this->getConjugationData($word);
        $base = $data['base'];

        $templateIndex = $data['index'] ?? $word;

        Assert::keyExists(
            $this->conjugationTemplates,
            $templateIndex,
            'No conjugations template for index: ' . $templateIndex . '.'
        );

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

        Assert::notNull($gender, 'Undefined gender.');

        $template = null;

        switch ($time) {
            case self::INFINITIVE:
                $template = $templateData[self::INFINITIVE];
                break;

            case self::PAST:
                $template = $templateData[self::PAST][$gender - 1];
                break;

            case self::PRESENT:
                $template = $templateData[self::PRESENT][$person - 1][$number - 1];
                break;

            case self::FUTURE:
                if (array_key_exists(self::FUTURE, $templateData)) {
                    $template = $templateData[self::FUTURE][$person - 1][$number - 1];
                } else {
                    $template = $this->futureConjugationTemplates[$person - 1][$number - 1] . ' ' . $templateData[self::INFINITIVE];
                }

                break;
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
