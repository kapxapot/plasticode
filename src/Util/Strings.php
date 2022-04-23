<?php

namespace Plasticode\Util;

class Strings
{
    public const SPACE_CHAR = '_';
    private const TAG_PARTS_DELIMITER = '|';

    /**
     * Replaces special characters with spaces.
     * 
     * Replaces SPACE_CHAR ('_' or $space) with ' '.
     * Also strips slashes.
     * 
     * @param string $str String to process
     * @param string $space Custom character to replace, '_' by default
     */
    public static function toSpaces(
        string $str = null,
        string $space = self::SPACE_CHAR
    ): string
    {
        $str = stripslashes($str);
        return preg_replace('/' . $space . '/', ' ', $str);
    }

    /**
     * Replaces spaces with special characters.
     * 
     * Replaces '\s+' with SPACE_CHAR ('_' or $space).
     * 
     * @param string $str String to process
     * @param string $space Custom replacement character, '_' by default
     */
    public static function fromSpaces(
        string $str = null,
        string $space = self::SPACE_CHAR
    ): string
    {
        return preg_replace('/\s+/u', $space, $str);
    }

    /**
     * Converts 'snake_case'/'Snake_Case'/'SnakeCase' to 'snakeCase'.
     */
    public static function toCamelCase(string $str): string
    {
        return lcfirst(self::toPascalCase($str));
    }

    /**
     * Converts 'snake_case' to 'SnakeCase'.
     */
    public static function toPascalCase(string $str): string
    {
        return str_replace('_', '', ucwords($str, '_'));
    }

    /**
     * Converts 'PascalCase'/'camelCase' to 'pascal_case'/'camel_case'.
     */
    public static function toSnakeCase(string $str): string
    {
        return ltrim(
            mb_strtolower(
                preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $str)
            ),
            '_'
        );
    }

    /**
     * Trims, converts to lower case and squishes spaces.
     *
     * @param string|null $space Character to squish spaces to
     */
    public static function normalize(
        ?string $str,
        ?string $space = null
    ): ?string
    {
        if (strlen($str) == 0) {
            return $str;
        }

        $space = $space ?? ' ';

        $str = mb_strtolower($str);
        $str = self::fromSpaces($str, $space);
        $str = preg_replace('#(' . $space . '){2,}#', $space, $str);
        $str = trim($str, $space);

        return $str;
    }

    /**
     * Converts '   Tag,    AnotherTag ' to ['tag', 'anothertag'].
     * 
     * String is exploded by ',' and every chunk is normalized.
     * This is done before saving tags to database.
     *
     * @return string[]
     */
    public static function toTags(?string $str): array
    {
        if (strlen($str) == 0) {
            return [];
        }

        $tags = array_map(
            fn (string $t) => self::normalize($t),
            self::explode($str)
        );

        return Arrays::clean(array_unique($tags));
    }

    /**
     * Explodes string (by ',' by default) and "trims" it.
     * 
     * @return string[]
     */
    public static function explode(?string $str, ?string $delimiter = null): array
    {
        if (strlen($str) == 0) {
            return [];
        }

        if (strlen($delimiter) == 0) {
            $delimiter = ',';
        }

        return Arrays::trim(
            explode($delimiter, $str)
        );
    }

    /**
     * Breaks string by spaces and trims the resulting array.
     *
     * @return string[]
     */
    public static function toWords(string $str): array
    {
        return Arrays::trim(preg_split("/\s/", $str));
    }

    /**
     * Explodes string by $delimiter and returns last chunk.
     */
    public static function lastChunk(string $str, string $delimiter): string
    {
        $chunks = explode($delimiter, $str);
        
        return Arrays::last($chunks);
    }

    /**
     * Strips HTML tags using static::stripTags(),
     * then truncates string to (limit) Unicode characters.
     */
    public static function stripTrunc(string $str, int $limit): string
    {
        $str = self::stripTags($str);

        return self::trunc($str, $limit);
    }

    /**
     * Strips HTML tags inserting spaces instead of them and trims the string.
     */
    public static function stripTags(string $str): string
    {
        $str = str_replace('<', ' <', $str);
        $str = strip_tags($str);
        $str = preg_replace('/\s+/', ' ', $str);

        return trim($str);
    }

    /**
     * Truncates string to (limit) Unicode characters.
     */
    public static function trunc(string $str, int $limit): string
    {
        return mb_substr($str, 0, $limit);
    }

    /**
     * Returns first char.
     */
    public static function first(string $str): string
    {
        return mb_substr($str, 0, 1);
    }

    /**
     * Returns last char.
     */
    public static function last(string $str): string
    {
        return mb_substr($str, -1);
    }

    /**
     * Builds hash tags string based on tags array.
     * 
     * ['abc', 'def ghi'] => '#abc #defghi'
     * 
     * @param string[] $tags Array of tags
     * @return string Hashed tags string
     */
    public static function hashTags(array $tags): string
    {
        return implode(
            ' ',
            array_map(
                function ($tag) {
                    return '#' . self::toAlphaNum($tag);
                },
                $tags
            )
        );
    }

    /**
     * Checks if string starts with 'http'.
     */
    public static function isUrl(?string $str): bool
    {
        return self::startsWith($str, 'http');
    }

    /**
     * Checks if string starts with 'http' or '/'.
     */
    public static function isUrlOrRelative(?string $str): bool
    {
        return self::isUrl($str) || self::startsWith($str, '/');
    }

    /**
     * Checks if a string starts with any mask from the list.
     *
     * @param string|null $str String to test.
     * @param string[] $masks Array of string masks.
     * @return bool True, if matches.
     */
    public static function startsWithAny(?string $str, array $masks): bool
    {
        if (strlen($str) == 0 || empty($masks)) {
            return false;
        }

        foreach ($masks as $mask) {
            if (self::startsWith($str, $mask)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a string starts with a given mask.
     * 
     * @param string|null $str String to test.
     * @param string $mask String mask.
     * @return bool True, if matches.
     */
    public static function startsWith(?string $str, string $mask): bool
    {
        if (strlen($str) == 0) {
            return false;
        }

        return strpos($str, $mask) === 0;
    }

    /**
     * Checks if a string ends with any mask from the list.
     *
     * @param string|null $str String to test.
     * @param string[] $masks Array of string masks.
     * @return bool True, if matches.
     */
    public static function endsWithAny(?string $str, array $masks): bool
    {
        if (strlen($str) == 0 || empty($masks)) {
            return false;
        }

        foreach ($masks as $mask) {
            if (self::endsWith($str, $mask)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a string ends with a given mask.
     * 
     * @param string|null $str String to test
     * @param string $mask String mask
     * @return bool True, if matches
     */
    public static function endsWith(?string $str, string $mask): bool
    {
        if (strlen($str) == 0) {
            return false;
        }

        return mb_substr($str, -strlen($mask)) == $mask;
    }

    /**
     * Compares two strings case-insensitive.
     *
     * @return integer > 0, if str1 is bigger, < 0 if str2, 0 if equal
     */
    public static function compare(string $str1, string $str2): int
    {
        return strcasecmp($str1, $str2);
    }

    /**
     * Removes all symbols from a string except letters, digits and '_'.
     */
    public static function toAlphaNum(?string $str): ?string
    {
        return preg_replace('/[^\w]/u', '', $str);
    }

    /**
     * Builds tag in format [[prefix:part1|part2]].
     */
    public static function doubleBracketsTag(
        ?string $prefix,
        string ...$parts
    ): string
    {
        $prefixPart = $prefix ? $prefix . ':' : '';
        $codePart = self::joinTagParts($parts);
        
        return '[[' . $prefixPart . $codePart . ']]';
    }

    /**
     * Joins strings with '|'.
     *
     * @param string[] $parts
     */
    public static function joinTagParts(array $parts): string
    {
        return implode(self::TAG_PARTS_DELIMITER, $parts);
    }

    /**
     * Cleans string and removes non-UTF-8 characters & control characters.
     *
     * @param boolean $control Remove control characters? True by default.
     */
    public static function toUtf8(?string $string, bool $control = true): ?string
    {
        $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);

        if ($control === true) {
            $string = preg_replace('~\p{C}+~u', '', $string);
        }

        return $string;
    }

    /**
     * Appends query param to http request string.
     *
     * @param mixed $value
     */
    public static function appendQueryParam(
        string $request,
        string $name,
        $value
    ): string
    {
        $delim = strpos($request, '?') !== false ? '&' : '?';
        return $request . $delim . $name . '=' . $value;
    }

    /**
     * Converts string to slug, that allows only [a-z0-9\-]
     * and must start from alphanumeric character.
     */
    public static function toSlug(string $rawSlug): ?string
    {
        // remove invalid characters
        $slug = preg_replace('/[^a-zA-Z0-9-\s]/', '', $rawSlug);

        // trim, lower case, squish spaces and replace them with '-'
        $slug = self::normalize($slug, '-');

        return $slug;
    }

    /**
     * Removes start from the string.
     */
    public static function trimStart(string $str, string $start): string
    {
        return self::startsWith($str, $start)
            ? mb_substr($str, strlen($start))
            : $str;
    }

    /**
     * Removes end from the string.
     */
    public static function trimEnd(string $str, string $end): string
    {
        return self::endsWith($str, $end)
            ? mb_substr($str, 0, mb_strlen($str) - mb_strlen($end))
            : $str;
    }

    /**
     * Checks if the string contains the mask.
     */
    public static function contains(?string $str, string $mask): bool
    {
        if (is_null($str)) {
            return false;
        }

        return strpos($str, $mask) !== false;
    }

    /**
     * Makes unicode string's first character upper-case.
     */
    public static function upperCaseFirst(?string $str): ?string
    {
        if (strlen($str) === 0) {
            return $str;
        }

        return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
    }

    /**
     * Makes unicode string's first character lower-case.
     */
    public static function lowerCaseFirst(?string $str): ?string
    {
        if (strlen($str) === 0) {
            return $str;
        }

        return mb_strtolower(mb_substr($str, 0, 1)) . mb_substr($str, 1);
    }
}
