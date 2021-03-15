<?php

namespace Plasticode\Validation;

use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ValidationRules
{
    /** @var array<string, callable> */
    private array $rules;

    public function __construct(
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->rules = $this->buildRules($settingsProvider);
    }

    /**
     * @return array<string, callable>
     */
    private function buildRules(
        SettingsProviderInterface $settingsProvider
    ): array
    {
        $notEmpty = fn () => Validator::notEmpty();
        $solid = fn () => $notEmpty()->noWhitespace();
        $alias = fn () => $solid()->alnum();

        return [
            'name' => fn () => $notEmpty()->alnum(),
            'alias' => $alias,
            'extendedAlias' => fn () => $solid()->regex('/^[\w]+$/'),
            'nullableAlias' => fn () => Validator::noWhitespace(),
            'text' => $notEmpty,
            'url' => $solid,
            'posInt' => fn () => Validator::numeric()->positive(),
            'image' => fn () => Validator::imageNotEmpty()->imageTypeAllowed(),
            'password' => fn () => Validator::noWhitespace()->length(
                $settingsProvider->get('password_min', 5)
            ),
            'login' => fn () => $alias()->length(
                $settingsProvider->get('login_min', 3),
                $settingsProvider->get('login_max', 20)
            ),
        ];
    }

    public function get(string $name, bool $optional = false): Validator
    {
        $rule = $this->rules[$name] ?? null;

        if (is_null($rule)) {
            throw new InvalidConfigurationException(
                'Validation rule \'' . $name . '\' not found.'
            );
        }

        return $optional
            ? $this->optional($rule())
            : $rule();
    }

    public function lat(string $add = ''): string
    {
        return "/^[\w {$add}]+$/";
    }
    
    public function cyr(string $add = ''): string
    {
        return "/^[\w\p{Cyrillic} {$add}]+$/u";
    }
    
    public function optional(Validatable $rule): Validator
    {
        return Validator::optional($rule);
    }
}
