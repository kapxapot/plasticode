<?php

namespace Plasticode;

use Psr\Container\ContainerInterface;

use Plasticode\Util\Arrays;

class Contained
{
    /**
     * DI container
     *
     * @var ContainerInterface ContainerInterface;
     */
    public $container;

    /**
     * Creates new Contained instance.
     * 
     * @param ContainerInterface $container Slim container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Returns object / array from container by property name
     *
     * @param string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        if ($this->container->{$property} || is_array($this->container->{$property})) {
            return $this->container->{$property};
        }
    }
    
    /**
     * Returns settings value
     *
     * @param string $path Path to settings value
     * @param mixed $default Default value
     * @return mixed
     */
    public function getSettings(string $path = null, $default = null)
    {
        $result = $this->container->get('settings');

        if ($path) {
            $result = Arrays::get($result, $path);
        }
        
        return $result ?? $default;
    }
}
