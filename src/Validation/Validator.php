<?php

namespace Plasticode\Validation;

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

use Plasticode\Contained;
use Plasticode\Validation\Rules\ContainerRule;

class Validator extends Contained {
	public $errors;

	public function validate($request, array $rules) {
		foreach ($rules as $field => $rule) {
			try {
				foreach ($rule->getRules() as $subRule) {
					if ($subRule instanceof ContainerRule) {
	    				$subRule->setContainer($this->container);
					}
				}
				
				$name = $this->translator->translateField($field);

				$rule->setName($name)->assert($request->getParam($field));
			}
			catch (NestedValidationException $e) {
				$e->setParam('translator', [ $this->translator, 'translateMessage' ]);
				$this->errors[$field] = $e->getMessages();
			}
		}

		return $this;
	}
	
	public function failed() {
		return !empty($this->errors);
	}
}
