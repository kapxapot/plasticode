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
	
	public static function toCamelCase($str)
	{
	    return lcfirst(self::toPascalCase($str));
	}
	
	public static function toSnakeCase($str)
	{
	    return ltrim(mb_strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $str)), '_');
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
		}, self::explode($str));
		
		return array_unique($tags);
	}
	
	public static function trimArray(array $strArray) : array
	{
	    $array = array_map(function($chunk) {
            return trim($chunk);
        }, $strArray);
        
        return Arrays::clean($array);
	}
	
	public static function explode($str, string $delimiter = ',') : array
	{
        return self::trimArray(explode($delimiter, $str));
	}
	
	public static function toWords(string $str) : array
	{
	    return self::trimArray(preg_split("/\s/", $str));
	}
	
	public static function lastChunk(string $str, string $delimiter) : string
	{
	    $chunks = explode($delimiter, $str);
	    
	    return Arrays::last($chunks);
	}
	
	/**
	 * Truncates string to (limit) Unicode characters
	 * 
	 * @param string $str String to truncate
	 * @param int $limit Desired string length
	 *
	 * @return string Truncated string
	 */
	public static function trunc($str, $limit, $stripTags = true) : string
	{
		return mb_substr($str, 0, $limit);
	}
	
	/**
	 * Truncates string to (limit) Unicode characters and strips html tags
	 * 
	 * @param string $str String to truncate
	 * @param int $limit Desired string length
	 *
	 * @return string Truncated string
	 */
	public static function stripTrunc($str, $limit)
	{
		$str = strip_tags($str);
	    return self::trunc($str, $limit);
	}
	
	/**
	 * Returns first char.
	 */
	public static function first($str)
	{
	    return mb_substr($str, 0, 1);
	}
	
	/**
	 * Returns last char.
	 */
	public static function last($str)
	{
	    return mb_substr($str, -1);
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
    
    /**
     * Checks if a string ends with a given mask.
     * 
     * @param string $str String to test
     * @param string $mask String mask
     * 
     * $return bool True, if matches
     */
    public static function endsWith($str, $mask)
    {
        return mb_substr($str, -strlen($mask)) == $mask;
    }
    
    public static function compare(string $str1, string $str2)
    {
        return strcasecmp($str1, $str2);
    }
}
