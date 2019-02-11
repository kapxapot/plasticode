<?php

namespace Plasticode\Core;

class Translator
{
	private $dictionaries;

	public function __construct($dictionaries)
	{
		$this->dictionaries = $dictionaries;
	}

	public function translate($value)
	{
		return $this->dictionaries[$value] ?? $value;
	}
}
