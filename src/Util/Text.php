<?php

namespace Plasticode\Util;

class Text
{
    /** @var string */
    public const Br = '<br/>';

    /** @var string */
    public const BrBr = self::Br . self::Br;

    private const BrPattern = '\<br\s*\/?\>';

    /** @var string */
    public const POpen = '<p>';

    /** @var string */
    public const PClose = '</p>';

    /** @var string[] */
    private const NewLines = ["\r\n", "\r", "\n"];

    /**
     * Breaks text into array of lines.
     * 
     * @param string|null $text
     * @return string[]
     */
    public static function toLines(?string $text) : array
    {
        $newLines = implode('|', self::NewLines);

        return strlen($text) > 0
            ? preg_split("/" . $newLines . "/", $text)
            : [];
    }

    /**
     * Joins array of lines into text.
     * 
     * @param string[] $lines
     * @return string
     */
    public static function fromLines(array $lines) : string
    {
        return implode(PHP_EOL, $lines);
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
     * 
     * @param string $text
     * @return string
     */
    public static function trimBrs(string $text) : string
    {
        return self::trimPattern(self::BrPattern, $text);
    }

    /**
     * Trims <br/>s and new line symbols from start and end of text.
     *
     * @param string $text
     * @return string
     */
    public static function trimNewLinesAndBrs(string $text) : string
    {
        $patterns = self::NewLines;
        $patterns[] = self::BrPattern;

        return self::trimMultiPattern($patterns, $text);
    }

    /**
     * Trims the string both from start & end using regex patterns.
     *
     * @param string[] $patterns
     * @param string $text
     * @return string
     */
    public static function trimMultiPattern(array $patterns, string $text) : string
    {
        $pattern = implode('|', $patterns);

        return self::trimPattern($pattern, $text);
    }

    /**
     * Trims the string both from start & end using regex pattern.
     *
     * @param string $pattern
     * @param string $text
     * @return string
     */
    public static function trimPattern(string $pattern, string $text) : string
    {
        $text = preg_replace("/^(" . $pattern . ")*/s", '', $text);
        $text = preg_replace("/(" . $pattern . ")*$/s", '', $text);

        return $text;
    }

    /**
     * ~\n -> <br/>.
     *
     * @param string $text
     * @return string
     */
    public static function newLinesToBrs(string $text) : string
    {
        return str_replace(self::NewLines, self::Br, $text);
    }

    /**
     * <br/>{3,} -> <br/><br/>.
     *
     * @param string $text
     * @return string
     */
    public static function squishBrs(string $text) : string
    {
        return preg_replace('#(' . self::Br . '){3,}#', self::BrBr, $text);
    }

    /**
     * Changes <br/>{2,} to </p><p>. Also ensures that text is wrapped in <p>...</p>.
     *
     * @param string $text
     * @return string
     */
    public static function brsToPs(string $text) : string
    {
        $text = self::squishBrs($text);

        $text = str_replace(self::BrBr, self::PClose . self::POpen, $text);

        if (!Strings::startsWith($text, self::POpen)) {
            $text = self::POpen . $text;
        }

        if (!Strings::endsWith($text, self::PClose)) {
            $text = $text . self::PClose;
        }

        return $text;
    }

    /**
     * Makes <a href=""></a> tag links absolute from relative.
     *
     * @param string $text
     * @param string $baseUrl
     * @return string
     */
    public static function toAbsoluteUrls(string $text, string $baseUrl) : string
    {
        $text = str_replace('=/', '=' . $baseUrl, $text);
        $text = str_replace('="/', '="' . $baseUrl, $text);
        
        return $text;
    }

    /**
     * Applies the array of regex replacements.
     *
     * @param string $text
     * @param array $replaces Key/value pair of replaces from/to
     * @return string
     */
    public static function applyRegexReplaces(string $text, array $replaces) : string
    {
        foreach ($replaces as $key => $value) {
            $text = preg_replace('#(' . $key . ')#', $value, $text);
        }
        
        return $text;
    }
}
