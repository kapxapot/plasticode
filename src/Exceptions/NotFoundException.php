<?php

namespace Plasticode\Exceptions;

class NotFoundException extends \Exception implements IApiException {
    public function __construct($message = null) {
        parent::__construct($message ?? 'Not found.');
    }
	
	public function GetErrorCode() {
		return 404;
	}
}
