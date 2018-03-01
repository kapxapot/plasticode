<?php

namespace Plasticode\Exceptions;

class AuthenticationException extends \Exception implements IApiException {
    public function __construct($message = 'Access denied.') {
        parent::__construct($message);
    }
	
	public function GetErrorCode() {
		return 401;
	}
}
