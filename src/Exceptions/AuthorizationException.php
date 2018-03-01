<?php

namespace Plasticode\Exceptions;

class AuthorizationException extends \Exception implements IApiException {
    public function __construct($message = 'Insufficient access rights.') {
        parent::__construct($message);
    }
	
	public function GetErrorCode() {
		return 401;
	}
}
