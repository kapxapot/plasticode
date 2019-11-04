<?php

namespace Plasticode\IO;

use Plasticode\Exceptions\IoException;
use Plasticode\Util\Strings;

class File
{
    public static function load(string $file) : string
    {
        $content = @file_get_contents($file);

        if ($content === false) {
            throw new IoException("Error reading file {$file}.");
        }

        return $content;
    }
    
    /**
     * Save file
     *
     * @param string $file
     * @param string $data
     * @return void
     */
    public static function save(string $file, string $data)
    {
        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($file, $data);
    }
    
    /**
     * Delete file
     *
     * @param string $file
     * @return void
     */
    public static function delete(string $file)
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    /**
     * Delete all files matching the mask
     *
     * @param string $mask
     * @param string $except
     * @return void
     */
    public static function cleanUp(string $mask, string $except = null)
    {
        foreach (glob($mask) as $toDel) {
            if (!is_dir($toDel) && $toDel != $except) {
                self::delete($toDel);
            }
        }
    }
    
    /**
     * Get file's extension. If there's none, return null
     */
    public static function getExtension(string $path) : ?string
    {
        $chunk = strrchr($path, ".");
        return $chunk ? substr($chunk, 1) : null;
    }
    
    /**
     * Return file name without extension
     */
    public static function getName(string $path) : string
    {
        $path = basename($path);
        $pos = strrpos($path, '.');
        return ($pos !== false)
            ? substr($path, 0, $pos)
            : $path;
    }
    
    /**
     * Combines file/directory path parts
     */
    public static function combine(...$parts) : ?string
    {
        $sep = DIRECTORY_SEPARATOR;
        $path = null;
        
        foreach ($parts as $part) {
            if (is_null($path)) {
                $path = $part;
            } else {
                $path = rtrim($path, $sep) . $sep . ltrim($part, $sep);
            }
        }

        return $path;
    }
    
    public static function exists(string $path) : bool
    {
        return file_exists($path);
    }

    /**
     * Checks if path is relative (starting from '..') and appends
     * base dir to it, otherwise doesn't change it.
     *
     * @param string $dir
     * @param string $path
     * @return string
     */
    public static function absolutePath(string $dir, string $path) : string
    {
        $sep = DIRECTORY_SEPARATOR;
        $path = ltrim($path, $sep);

        if (Strings::startsWith($path, '..')) {
            $path = self::combine($dir, $path);
        }

        return $path;
    }
}
