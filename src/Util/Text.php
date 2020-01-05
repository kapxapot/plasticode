<?php

namespace Plasticode\Util;

class Text
{
    public const Br = '<br/>';
    public const BrBr = self::Br . self::Br;

    public const POpen = '<p>';
    public const PClose = '</p>';

    public const Cut = '<!--cut-->';

    /**
     * Breaks text into array of lines.
     * 
     * @param string|null $text
     * 
     * @return string[]
     */
    public static function toLines(?string $text) : array
    {
        return strlen($text) > 0
            ? preg_split("/\r\n|\n|\r/", $text)
            : [];
    }

    /**
     * Joins array of lines into text.
     * 
     * @param string[] $lines
     * 
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
     * 
     * @return string[]
     */
    public static function trimLines(array $lines) : array
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
     * 
     * @return string
     */
    public static function trimBrs(string $text) : string
    {
        $br = '(\<br\s*\/\>)*';
        
        $text = preg_replace("/^{$br}/s", '', $text);
        $text = preg_replace("/{$br}$/s", '', $text);

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
        return str_replace(["\r\n", "\r", "\n"], self::Br, $text);
    }

    /**
     * <br/> x3+ -> <br/><br/>.
     *
     * @param string $text
     * @return string
     */
    public static function squishBrs(string $text) : string
    {
        return preg_replace('#(' . self::Br . '){3,}#', self::BrBr, $text);
    }

    /**
     * Changes <br/>{3,} to </p><p>. Also ensures that text is wrapped in <p>...</p>.
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
