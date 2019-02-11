<?php

namespace Plasticode\Util;

class Strings
{
	const SPACE_CHAR = '_';

	/**
	 * Replaces special characters with spaces.
	 * 
	 * Replaces SPACE_CHAR (or $space) with ' '.
	 * Also strips slashes.
	 * 
	 * @param string $str String to process
	 * @param string $space Custom character to replace, '_' by default
	 * @return string
	 */
	public static function toSpaces($str, $space = self::SPACE_CHAR)
	{
		$str = stripslashes($str);
		return preg_replace("/{$space}/", ' ', $str);
	}

	/**
	 * Replaces spaces with special characters.
	 * 
	 * Replaces '\s+' with SPACE_CHAR's (or $space's).
	 * 
	 * @param string $str String to process
	 * @param string $space Custom replacement character, '_' by default
	 * @return string
	 */
	public static function fromSpaces($str, $space = self::SPACE_CHAR)
	{
		return preg_replace('/\s+/u', $space, $str);
	}
	
	public static function toPascalCase($str)
	{
		return str_replace('_', '', ucwords($str, '_'));
	}
	
	public static function normalize($str)
	{
		$str = trim($str);
		$str = mb_strtolower($str);
		$str = preg_replace('/\s+/', ' ', $str);
		
		return $str;
	}
	
	public static function toTags($str)
	{
		$tags = array_map(function($t) {
			return self::normalize($t);
		}, explode(',', $str));
		
		return array_unique($tags);
	}
	
	public static function explode($str, $delimiter = ',')
	{
	    if (!$str) {
	        return null;
	    }
	    else {
    	    return array_map(function($chunk) {
                return trim($chunk);
            }, explode($delimiter, $str));
	    }
	}
	
	/**
	 * Truncates string to (limit) Unicode characters and strips html tags (by default)
	 * 
	 * @param string $str String to truncate
	 * @param int $limit Desired string length
	 * @param bool $stripTags Set to false if you don't want to strip html tags
	 *
	 * @return string Truncated string
	 */
	public static function trunc($str, $limit, $stripTags = true)
	{
		if ($stripTags === true) {
			$str = strip_tags($str);
		}
		
		return mb_substr($str, 0, $limit);
	}
	
	/**
	 * Returns first char.
	 */
	public static function first($str)
	{
	    return mb_substr($str, 0, 1);
	}
	
	/**
	 * Builds hash tags string based on tags array
	 * 
	 * ['abc', 'def ghi'] => '#abc #defghi'
	 * 
	 * @param string[] $tags Array of tags
	 * 
	 * @return string Hashed tags string
	 */
	public static function hashTags($tags)
	{
	    return implode(' ', array_map(function ($tag) {
	        return '#' . str_replace(' ', '', $tag);
	    }, $tags));
	}
    
    /**
     * Checks if a string starts with any mask from the list.
     * 
     * @param string $str String to test
     * @param string[] $masks Array of string masks
     * 
     * $return bool True, if matches
     */
    public static function startsWithAny($str, $masks)
    {
        $matches = false;
        
        if (!empty($masks)) {
            foreach ($masks as $mask) {
                if (self::startsWith($str, $mask)) {
                    $matches = true;
                    break;
                }
            }
        }
        
        return $matches;
    }
    
    /**
     * Checks if a string starts with a given mask.
     * 
     * @param string $str String to test
     * @param string $mask String mask
     * 
     * $return bool True, if matches
     */
    public static function startsWith($str, $mask)
    {
        return strpos($str, $mask) === 0;
    }
}
