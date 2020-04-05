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
    private ContainerInterface $container;
    private TranslatorInterface $translator;

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
                $errors[$field] = $e->getMessages();
            }
        }

        return new ValidationResult($errors);
    }

    public function validateArray(array $data, array $rules) : ValidationResult
    {
        return $this->validate(
            fn ($field) => $data[$field] ?? null,
            $rules
        );
    }

    public function validateRequest(
        SlimRequest $request,
        array $rules
    ) : ValidationResult
    {
        return $this->validate(
            fn ($field) => $request->getParam($field),
            $rules
        );
    }
}
