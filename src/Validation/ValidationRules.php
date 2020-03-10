<?php

namespace Plasticode\Validation;

use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Interfaces\SettingsProviderInterface;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ValidationRules
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var array */
    private $rules;
    
    public function __construct(SettingsProviderInterface $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;

        $this->rules = $this->buildRules();
    }
    
    private function buildRules() : array
    {
        $notEmpty = function () {
            return Validator::notEmpty();
        };
        
        $solid = function () use ($notEmpty) {
            return $notEmpty()->noWhitespace();
        };

        $alias = function () use ($solid) {
            return $solid()->alnum();
        };
        
        return [
            'name' => function () use ($notEmpty) {
                return $notEmpty()->alnum();
            },
            'alias' => function () use ($alias) {
                return $alias();
            },
            'extendedAlias' => function () use ($solid) {
                return $solid()->regex('/^[\w]+$/');
            },
            'nullableAlias' => function () {
                return Validator::noWhitespace();
            },
            'text' => function () use ($notEmpty) {
                return $notEmpty();
            },
            'url' => function () use ($solid) {
                return $solid();
            },
            'posInt' => function () {
                return Validator::numeric()->positive();
            },
            'image' => function () {
                return Validator::imageNotEmpty()->imageTypeAllowed();
            },
            'password' => function () {
                $pwdMin = $this->settingsProvider->getSettings('password_min');

                return Validator::noWhitespace()->length($pwdMin);
            },
            'login' => function () use ($alias) {
                $loginMin = $this->settingsProvider->getSettings('login_min');
                $loginMax = $this->settingsProvider->getSettings('login_max');

                return $alias()->length($loginMin, $loginMax);
            },
        ];
    }
    
    public function get(string $name, bool $optional = false) : Validator
    {
        if (!array_key_exists($name, $this->rules)) {
            throw new InvalidConfigurationException(
                'Validation rule \'' . $name . '\' not found.'
            );
        }
        
        $rule = $this->rules[$name]();
        
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
