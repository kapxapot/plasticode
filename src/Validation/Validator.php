<?php

namespace Plasticode\Validation;

use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Exceptions\NestedValidationException;

/**
 * Validator with the optional translation of messages.
 */
class Validator implements ValidatorInterface
{
    private ?TranslatorInterface $translator;

    public function __construct(
        ?TranslatorInterface $translator = null
    )
    {
        $this->translator = $translator;
    }

    private function validate(callable $getField, array $rules): ValidationResult
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            try {
                $name = $this->translator
                    ? $this->translator->translate($field)
                    : $field;

                $value = $getField($field);

                $rule->setName($name)->assert($value);
            } catch (NestedValidationException $e) {
                if ($this->translator) {
                    $e->setParam('translator', [$this->translator, 'translate']);
                }

                $errors[$field] = $e->getMessages();
            }
        }

        return new ValidationResult($errors);
    }

    public function validateRequest(
        ServerRequestInterface $request,
        array $rules
    ): ValidationResult
    {
        $params = in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])
            ? $request->getParsedBody()
            : $request->getQueryParams();

        return $this->validateArray($params, $rules);
    }

    public function validateArray(array $data, array $rules): ValidationResult
    {
        return $this->validate(
            fn ($field) => $data[$field] ?? null,
            $rules
        );
    }
}
