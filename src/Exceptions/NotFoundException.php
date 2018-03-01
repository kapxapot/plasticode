<?php

namespace Plasticode\Exceptions;

class NotFoundException extends \Exception implements IApiException {
    public function __construct($message = 'Not found.') {
        parent::__construct($message);
    }
	
	public function GetErrorCode() {
		return 404;
	}
}
