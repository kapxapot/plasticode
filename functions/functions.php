<?php

if (!function_exists('dd')) {
    /**
     * var_dump() + die().
     *
     * @param mixed $var
     */
    function dd($var) : void
    {
        var_dump($var);
        die();
    }
}

if (!function_exists('isCallable')) {
    /**
     * Checks if the variable is callable and not a string
     * that represents a function such as 'date'.
     *
     * @param mixed $func
     */
    function isCallable($func) : bool
    {
        return is_callable($func) && !is_string($func);
    }
}

if (!function_exists('isScalar')) {
    /**
     * Checks if the variable is a scalar or null.
     *
     * @param mixed $var
     */
    function isScalar($var) : bool
    {
        return is_scalar($var) || is_null($var);
    }
}
