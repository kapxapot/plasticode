<?php

namespace Plasticode\Semantics;

use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Util\Arrays;
use Plasticode\Util\Strings;

class Sentence
{
    const PERIOD = '.';
    const ELLIPSIS = '...';
    const EXCLAMATION_MARK = '!';
    const QUESTION_MARK = '?';

    const COMMA_DELIMITER = ', ';
    const AND_DELIMITER = ' и ';

    /**
     * Joins parts into a sentence such as "a, b, c".
     *
     * @param array|ArrayableInterface $array
     */
    public static function join(
        $array,
        ?string $commaDelimiter = null
    ): string
    {
        return implode(
            $commaDelimiter ?? self::COMMA_DELIMITER,
            Arrays::adopt($array)
        );
    }

    /**
     * Joins homogeneous parts into a sentence such as "a, b и c".
     *
     * @param array|ArrayableInterface $array
     */
    public static function homogeneousJoin(
        $array,
        ?string $commaDelimiter = null,
        ?string $andDelimiter = null
    ): string
    {
        $chunks = Arrays::adopt($array);

        $commaDelimiter ??= self::COMMA_DELIMITER;
        $andDelimiter ??= self::AND_DELIMITER;

        // a
        // a и b
        // a, b и c

        $result = '';
        $count = count($chunks);

        for ($index = 1; $index <= $count; $index++) {
            $chunk = $chunks[$count - $index];

            switch ($index) {
                case 1:
                    $result = $chunk;
                    break;

                case 2:
                    $result = $chunk . $andDelimiter . $result;
                    break;

                default:
                    $result = $chunk . $commaDelimiter . $result;
            }
        }

        return $result;
    }

    /**
     * Ensures that the string ends with one period ('.')
     * or any other allowed ending characters such as '!', '?', '...'.
     */
    public static function terminate(string $str): string
    {
        if (Strings::endsWithAny($str, [self::EXCLAMATION_MARK, self::QUESTION_MARK])) {
            return $str;
        }

        if (Strings::endsWith($str, self::ELLIPSIS)) {
            return trim($str, self::PERIOD) . self::ELLIPSIS;
        }

        return trim($str, self::PERIOD) . self::PERIOD;
    }

    /**
     * Builds a sentence from the given parts, making the first part upper-case
     * and all other parts lower-case.
     *
     * - Doesn't terminate the sentence by default.
     * - In case of no parts returns an empty string.
     *
     * @param array|ArrayableInterface|null $parts
     */
    public static function buildCased($parts, bool $terminate = false): string
    {
        $partsArray = Arrays::adopt($parts);

        if (count($partsArray ?? []) == 0) {
            return '';
        }

        $firstPart = Arrays::first($partsArray);
        $otherParts = Arrays::skip($partsArray, 1);

        $newParts = [
            Strings::upperCaseFirst($firstPart),
            ...array_map(
                fn (string $part) => Strings::lowerCaseFirst($part),
                $otherParts
            )
        ];

        $sentence = implode('', $newParts);

        return $terminate
            ? self::terminate($sentence)
            : $sentence;
    }
}
