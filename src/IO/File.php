<?php

namespace Plasticode\IO;

class File
{
	public static function load(string $file)
	{
        $content = @file_get_contents($file);

        if ($content === false) {
            throw new \Exception("Error reading file {$file}.");
        }

		return $content;
	}
	
	public static function save(string $file, $data)
	{
	    $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
	    file_put_contents($file, $data);
	}
	
	public static function delete(string $file)
	{
		if (file_exists($file)) {
			unlink($file);
		}
	}
	
	public static function cleanUp(string $mask, $except = null)
	{
		foreach (glob($mask) as $toDel) {
			if (!is_dir($toDel) && $toDel != $except) {
				self::delete($toDel);
			}
		}
	}
	
	/**
	 * Returns file extension. If there's none, return null.
	 */
	public static function getExtension(string $path)
	{
        $chunk = strrchr($path, ".");
        return $chunk ? substr($chunk, 1) : null;
	}
	
	/**
	 * Returns file name without extension.
	 */
	public static function getName(string $path)
	{
	    $path = basename($path);
        $pos = strrpos($path, '.');
        return ($pos !== false)
            ? substr($path, 0, $pos)
            : $path;
	}
	
	/**
	 * Combines file/directory path parts.
	 */
	public static function combine(...$parts)
	{
		$sep = DIRECTORY_SEPARATOR;
		$path = null;
	    
	    foreach ($parts as $part) {
	        if ($path === null) {
	            $path = $part;
	        } else {
	            $path = rtrim($path, $sep) . $sep . ltrim($part, $sep);
	        }
	    }

        return $path;
	}
	
	public static function exists(string $path)
	{
	    return file_exists($path);
	}
}
