<?php

namespace Plasticode\Core\Interfaces;

interface CacheInterface
{
    /**
     * Returns the cached value by given path.
     *
     * @param string $path
     * @return mixed|null
     */
    public function get(string $path);
    
    /**
     * Set the cached value by given path.
     *
     * @param string $path
     * @param mixed $value
     * @return void
     */
    public function set(string $path, $value) : void;
    
    /**
     * Checks if the cached values exists (and not null).
     *
     * @param string $path
     * @return boolean
     */
    public function exists(string $path) : bool;
    
    /**
     * Deletes the cached value if it exists.
     *
     * @param string $path
     * @return void
     */
    public function delete(string $path) : void;

    /**
     * Returns the cached value if it exists, otherwise creates it and saves it.
     * Can be forced to update even if the value is cached.
     *
     * @param string $path
     * @param \Closure $func
     * @param boolean $forced
     * @return mixed|null
     */
    public function getCached(string $path, \Closure $func, bool $forced = false);
}
