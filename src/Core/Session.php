<?php

namespace Plasticode\Core;

use Plasticode\Core\Interfaces\SessionInterface;

class Session implements SessionInterface
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;

        if (!isset($_SESSION[$this->name])) {
            $_SESSION[$this->name] = [];
        }
    }
    
    public function get(string $key)
    {
        return $_SESSION[$this->name][$key] ?? null;
    }

    public function set(string $key, $value) : void
    {
        $_SESSION[$this->name][$key] = $value;
    }
    
    public function delete(string $key) : void
    {
        unset($_SESSION[$this->name][$key]);
    }
    
    public function getAndDelete(string $key)
    {
        $value = $this->get($key);
        $this->delete($key);
        
        return $value;
    }
}
