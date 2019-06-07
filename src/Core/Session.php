<?php

namespace Plasticode\Core;

class Session
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;

        if (!isset($_SESSION[$this->name])) {
            $_SESSION[$this->name] = [];
        }
    }
    
    public function get($key)
    {
        return $_SESSION[$this->name][$key] ?? null;
    }

    public function set($key, $value)
    {
        $_SESSION[$this->name][$key] = $value;
    }
    
    public function delete($key)
    {
        unset($_SESSION[$this->name][$key]);
    }
    
    public function getAndDelete($key)
    {
        $value = $this->get($key);
        $this->delete($key);
        
        return $value;
    }
}
