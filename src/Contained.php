<?php

namespace Plasticode;

use Plasticode\Util\Arrays;

class Contained
{
	public $container;

	/**
	 * Creates new Contained instance.
	 * 
	 * @param ContainerInterface $container Slim container
	 */
	public function __construct($container)
	{
		$this->container = $container;
	}
	
	public function __get($property)
	{
		if ($this->container->{$property} || is_array($this->container->{$property})) {
			return $this->container->{$property};
		}
	}
	
	public function getSettings($path = null)
	{
		$result = $this->container->get('settings');

		if ($path) {
			$result = Arrays::get($result, $path);
		}
		
		return $result;
	}
}
