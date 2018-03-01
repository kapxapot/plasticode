<?php

namespace Plasticode\Validation\Rules;

use Plasticode\Data\Tables;

class EmailAvailable extends TableFieldAvailable {
	public function __construct($id = null) {
		parent::__construct(Tables::USERS, 'email', $id);
	}
}
