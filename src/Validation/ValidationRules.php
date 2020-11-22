<?php

namespace Plasticode\Validation;

use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Exceptions\InvalidConfigurationException;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ValidationRules
{
    /** @var array<string, Validator> */
    private array $rules;

    public function __construct(
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->rules = $this->buildRules($settingsProvider);
    }

    /**
     * @return array<string, Validator>
     */
    private function buildRules(
        SettingsProviderInterface $settingsProvider
    ) : array
    {
        $notEmpty = Validator::notEmpty();
        $solid = $notEmpty->noWhitespace();
        $alias = $solid->alnum();

        return [
            'name' => $notEmpty->alnum(),
            'alias' => $alias,
            'extendedAlias' => $solid->regex('/^[\w]+$/'),
            'nullableAlias' => Validator::noWhitespace(),
            'text' => $notEmpty,
            'url' => $solid,
            'posInt' => Validator::numeric()->positive(),
            'image' => Validator::imageNotEmpty()->imageTypeAllowed(),
            'password' => Validator::noWhitespace()->length(
                $settingsProvider->get('password_min', 5)
            ),
            'login' => $alias->length(
                $settingsProvider->get('login_min', 3),
                $settingsProvider->get('login_max', 20)
            ),
        ];
    }

    public function get(string $name, bool $optional = false) : Validator
    {
        $rule = $this->rules[$name] ?? null;

        if (is_null($rule)) {
            throw new InvalidConfigurationException(
                'Validation rule \'' . $name . '\' not found.'
            );
        }

        return $optional
            ? $this->optional($rule)
            : $rule;
    }

    public function lat(string $add = '') : string
    {
        return "/^[\w {$add}]+$/";
    }
    
    public function cyr(string $add = '') : string
    {
        return "/^[\w\p{Cyrillic} {$add}]+$/u";
    }
    
    public function optional(Validatable $rule) : Validator
    {
        return Validator::optional($rule);
    }
}
