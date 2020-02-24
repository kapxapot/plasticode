<?php

namespace Plasticode\Core\Interfaces;

interface SessionInterface
{
    /**
     * Returns session value.
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * Sets session value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value) : void;

    /**
     * Deletes session value.
     *
     * @param string $key
     * @return void
     */
    public function delete(string $key) : void;
    
    /**
     * Returns session value and deletes it immediately.
     *
     * @param string $key
     * @return mixed
     */
    public function getAndDelete(string $key);
}
