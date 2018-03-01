<?php

namespace Plasticode\Exceptions;

class ValidationException extends \Exception {
	public $errors;
	
	public function __construct($errors) {
		$this->errors = $errors;
	}
}
