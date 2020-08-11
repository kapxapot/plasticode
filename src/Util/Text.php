<?php

namespace Plasticode\Util;

class Text
{
    public const BR = '<br/>';
    public const BR_BR = self::BR . self::BR;
    private const BR_PATTERN = '\<br\s*\/?\>';

    public const P_OPEN = '<p>';
    public const P_CLOSE = '</p>';

    /** @var string[] */
    private const NEW_LINES = ["\r\n", "\r", "\n"];

    /**
     * Breaks text into array of lines.
     * 
     * @return string[]
     */
    public static function toLines(?string $text) : array
    {
        $newLines = implode('|', self::NEW_LINES);

        return strlen($text) > 0
            ? preg_split("/" . $newLines . "/", $text)
            : [];
    }

    /**
     * Joins array of lines into text using the provided delimiter (PHP_EOL by default).
     * 
     * @param string[] $lines
     */
    public static function join(array $lines, ?string $delimiter = null) : string
    {
        return implode($delimiter ?? PHP_EOL, $lines);
    }

    /**
     * Joins array of lines into text using double PHP_EOL as a delimiter.
     * 
     * @param string[] $lines
     */
    public static function sparseJoin(array $lines) : string
    {
        return implode(PHP_EOL . PHP_EOL, $lines);
    }

    /**
     * Removes empty lines from start and end of array.
     * 
     * @param string[] $lines
     * @return string[]
     */
    public static function trimEmptyLines(array $lines) : array
    {
        while (count($lines) > 0 && strlen($lines[0]) == 0) {
            array_shift($lines);
        }

        while (count($lines) > 0 && strlen($lines[count($lines) - 1]) == 0) {
            array_pop($lines);
        }

        return $lines;
    }

    /**
     * Trims <br/>s from start and end of text.
     */
    public static function trimBrs(string $text) : string
    {
        return self::trimPattern(self::BR_PATTERN, $text);
    }

    /**
     * Trims <br/>s and new line symbols from start and end of text.
     */
    public static function trimNewLinesAndBrs(string $text) : string
    {
        $patterns = self::NEW_LINES;
        $patterns[] = self::BR_PATTERN;

        return self::trimMultiPattern($patterns, $text);
    }

    /**
     * Trims the string both from start & end using regex patterns.
     *
     * @param string[] $patterns
     */
    public static function trimMultiPattern(array $patterns, string $text) : string
    {
        $pattern = implode('|', $patterns);

        return self::trimPattern($pattern, $text);
    }

    /**
     * Trims the string both from start & end using regex pattern.
     */
    public static function trimPattern(string $pattern, string $text) : string
    {
        $text = preg_replace("/^(" . $pattern . ")*/s", '', $text);
        $text = preg_replace("/(" . $pattern . ")*$/s", '', $text);

        return $text;
    }

    /**
     * ~\n -> <br/>.
     */
    public static function newLinesToBrs(string $text) : string
    {
        return str_replace(self::NEW_LINES, self::BR, $text);
    }

    /**
     * <br/>{3,} -> <br/><br/>.
     */
    public static function squishBrs(string $text) : string
    {
        return preg_replace(
            '/(' . self::BR_PATTERN . '){3,}/',
            self::BR_BR,
            $text
        );
    }

    /**
     * Changes <br/>{2,} to </p><p>. Also ensures that text is wrapped in <p>...</p>.
     */
    public static function brsToPs(string $text) : string
    {
        $text = self::squishBrs($text);

        $text = str_replace(self::BR_BR, self::P_CLOSE . self::P_OPEN, $text);

        if (!Strings::startsWith($text, self::P_OPEN)) {
            $text = self::P_OPEN . $text;
        }

        if (!Strings::endsWith($text, self::P_CLOSE)) {
            $text = $text . self::P_CLOSE;
        }

        return $text;
    }

    /**
     * Makes <a href=""></a> tag links absolute from relative.
     */
    public static function toAbsoluteUrls(string $text, string $baseUrl) : string
    {
        $baseUrl = rtrim($baseUrl, '/') . '/';

        $text = str_replace('=/', '=' . $baseUrl, $text);
        $text = str_replace('="/', '="' . $baseUrl, $text);

        return $text;
    }

    /**
     * Applies the array of regex replacements.
     *
     * @param array $replaces Key/value pair of replaces from/to
     */
    public static function applyRegexReplaces(string $text, array $replaces) : string
    {
        foreach ($replaces as $key => $value) {
            $text = preg_replace('/(' . $key . ')/', $value, $text);
        }

        return $text;
    }
}
