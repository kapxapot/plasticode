<?php

namespace Plasticode\Validation;

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

use Plasticode\Contained;
use Plasticode\Validation\Rules\ContainerRule;

class Validator extends Contained {
    public $errors;

    private function validate(\Closure $getField, array $rules) : self
    {
        $this->errors = [];
        
        foreach ($rules as $field => $rule) {
            try {
                foreach ($rule->getRules() as $subRule) {
                    if ($subRule instanceof ContainerRule) {
                        $subRule->setContainer($this->container);
                    }
                }
                
                $name = $this->translator->translate($field);
                $value = $getField($field);

                $rule->setName($name)->assert($value);
            }
            catch (NestedValidationException $e) {
                $e->setParam('translator', [ $this->translator, 'translate' ]);
                $this->errors[$field] = $e->getMessages();
            }
        }

        return $this;
    }
    
    public function validateArray(array $data, array $rules) : self
    {
        return $this->validate(
            function ($field) use ($data) {
                return $data[$field] ?? null;
            },
            $rules
        );
    }
    
    public function validateRequest($request, array $rules) : self
    {
        return $this->validate(
            function ($field) use ($request) {
                return $request->getParam($field);
            },
            $rules
        );
    }
    
    public function failed() : bool
    {
        return !empty($this->errors);
    }
}
