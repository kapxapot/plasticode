<?php

namespace Plasticode\Util;

class Text {
    /**
     * Breaks text into array of lines
     * 
     * @param string $text
     * 
     * @return string[]
     */
    static public function toLines($text) {
        return preg_split("/\r\n|\n|\r/", $text);
    }

    /**
     * Joins array of lines into text
     * 
     * @param string[] $lines
     * 
     * @return string
     */
    static public function fromLines($lines) {
        return implode(PHP_EOL, $lines);
    }
    
    /**
     * Removes empty lines from start and end of array
     * 
     * @param string[] $lines
     * 
     * @return string[]
     */
    static public function trimLines($lines) {
        while (count($lines) > 0 && strlen($lines[0]) == 0) {
            array_shift($lines);
        }
        
        while (count($lines) > 0 && strlen($lines[count($lines) - 1]) == 0) {
            array_pop($lines);
        }
        
        return $lines;
    }
    
    /**
     * Trims <br/>s from start and end of text
     * 
     * @param string $text
     * 
     * @return string
     */
    static public function trimBrs($text) {
        $br = '(\<br\s*\/\>)*';
        
        $text = preg_replace("/^{$br}/s", '', $text);
        $text = preg_replace("/{$br}$/s", '', $text);

        return $text;		
    }

    /**
     * Processes text as array of lines
     * 
     * 1. Breaks text into lines.
     * 2. Executes lines processing.
     * 3. Trims empty lines.
     * 4. Builds text back from lines.
     * 
     * @param string $text Source text
     * @param callable $process Function for processing lines (function(Array $lines) : Array)
     * @param bool $trimEmpty Set to false if you don't want to trim empty lines
     * 
     * @return string
     */
    static public function processLines($text, $process, $trimEmpty = true) {
        $lines = self::toLines($text);
        $result = $process($lines);

        if ($trimEmpty) {
            $result = self::trimLines($result);
        }
        
        $text = self::fromLines($result);

        return $text;
    }
}
