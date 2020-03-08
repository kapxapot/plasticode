<?php

namespace Plasticode\Validation;

use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\Rules\ContainerRule;
use Psr\Container\ContainerInterface;
use Respect\Validation\Exceptions\NestedValidationException;
use Slim\Http\Request as SlimRequest;

class Validator implements ValidatorInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        ContainerInterface $container,
        TranslatorInterface $translator
    )
    {
        $this->container = $container;
        $this->translator = $translator;
    }

    private function validate(\Closure $getField, array $rules) : ValidationResult
    {
        $errors = [];
        
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
                $e->setParam('translator', [$this->translator, 'translate']);
                $this->errors[$field] = $e->getMessages();
            }
        }

        return new ValidationResult($errors);
    }
    
    public function validateArray(array $data, array $rules) : ValidationResult
    {
        return $this->validate(
            function ($field) use ($data) {
                return $data[$field] ?? null;
            },
            $rules
        );
    }
    
    public function validateRequest(SlimRequest $request, array $rules) : ValidationResult
    {
        return $this->validate(
            function ($field) use ($request) {
                return $request->getParam($field);
            },
            $rules
        );
    }
}
