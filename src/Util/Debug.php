<?php

namespace Plasticode\Util;

use Exception;

class Debug
{
    /**
     * @return string[]
     */
    public static function exceptionTrace(Exception $ex): array
    {
        $lines = [];

        foreach ($ex->getTrace() as $trace) {
            $lines[] = ($trace['file'] ?? '') . ' (' . ($trace['line'] ?? '') . '), ' . ($trace['class'] ?? '') . ($trace['type'] ?? '') . $trace['function'];
        }

        return $lines;
    }
}
