<?php

namespace Plasticode\Validation\Rules;

use Plasticode\Data\Tables;

class LoginAvailable extends TableFieldAvailable {
	public function __construct($id = null) {
		parent::__construct(Tables::USERS, 'login', $id);
	}
}
