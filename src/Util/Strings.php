<?php

namespace Plasticode\Util;

class Strings {
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
	public static function toSpaces($str, $space = self::SPACE_CHAR) {
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
	public static function fromSpaces($str, $space = self::SPACE_CHAR) {
		return preg_replace('/\s+/u', $space, $str);
	}
	
	static public function toPascalCase($str) {
		return str_replace('_', '', ucwords($str, '_'));
	}
	
	static public function normalize($str) {
		$str = trim($str);
		$str = mb_strtolower($str);
		$str = preg_replace("/\s+/", " ", $str);
		
		return $str;
	}
	
	static public function toTags($str) {
		$tags = array_map(function($t) {
			return self::normalize($t);
		}, explode(',', $str));
		
		return array_unique($tags);
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
	static public function trunc($str, $limit, $stripTags = true) {
		if ($stripTags === true) {
			$str = strip_tags($str);
		}
		
		return mb_substr($str, 0, $limit);
	}
}
