<?php

namespace Plasticode\IO;

use Plasticode\Exceptions\IoException;
use Plasticode\Util\Strings;

class File
{
    /**
     * Directory separator
     */
    private const SEPARATOR = '/';

    public static function load(string $file) : string
    {
        $content = @file_get_contents($file);

        if ($content === false) {
            throw new IoException("Error reading file {$file}.");
        }

        return $content;
    }

    /**
     * Saves file.
     */
    public static function save(string $file, string $data) : void
    {
        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($file, $data);
    }

    /**
     * Deletes file.
     */
    public static function delete(string $file) : void
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Deletes all files matching the mask.
     */
    public static function cleanUp(string $mask, string $except = null) : void
    {
        foreach (glob($mask) as $toDel) {
            if (!is_dir($toDel) && $toDel != $except) {
                self::delete($toDel);
            }
        }
    }

    /**
     * Returns file's extension. If there's none, returns null.
     */
    public static function getExtension(string $path) : ?string
    {
        $chunk = strrchr($path, ".");
        return $chunk ? substr($chunk, 1) : null;
    }

    /**
     * Returns file name without extension.
     */
    public static function getName(string $path) : string
    {
        $path = basename($path);
        $pos = strrpos($path, '.');
        return ($pos !== false)
            ? substr($path, 0, $pos)
            : $path;
    }

    public static function exists(string $path) : bool
    {
        return file_exists($path);
    }

    /**
     * Combines file/directory path parts.
     */
    public static function combine(string ...$parts) : ?string
    {
        $sep = self::SEPARATOR;
        $path = null;

        foreach ($parts as $part) {
            $part = self::normalizePath($part);

            $path = is_null($path)
                ? $part
                : rtrim($path, $sep) . $sep . ltrim($part, $sep);
        }

        return $path;
    }

    /**
     * Checks if path is relative (starting from '..') and appends
     * base dir to it, otherwise doesn't change it.
     */
    public static function absolutePath(
        string $baseDir,
        string $relativePath
    ) : string
    {
        if (!preg_match('/^(\\\\|\/)?\.\./', $relativePath)) {
            return $relativePath;
        }

        return self::combine($baseDir, $relativePath);
    }

    /**
     * Normalizes all slashes to '/'.
     */
    public static function normalizePath(string $path) : string
    {
        return str_replace('\\', self::SEPARATOR, $path);
    }
}
