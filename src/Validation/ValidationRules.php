<?php

namespace Plasticode\Validation;

use Plasticode\Contained;
use Plasticode\Exceptions\InvalidConfigurationException;
use Respect\Validation\Validator as v;

class ValidationRules extends Contained
{
    private $rules;
    
    public function __construct($container)
    {
        parent::__construct($container);
        
        $this->rules = $this->buildRules();
    }
    
    private function buildRules()
    {
        $settings = $this->getSettings();
        
        $notEmpty = function () {
            return v::notEmpty();
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
                return v::noWhitespace();
            },
            'text' => function () use ($notEmpty) {
                return $notEmpty();
            },
            'url' => function () use ($solid) {
                return $solid();
            },
            'posInt' => function () {
                return v::numeric()->positive();
            },
            'image' => function () {
                return v::imageNotEmpty()->imageTypeAllowed();
            },
            'password' => function () use ($settings) {
                return v::noWhitespace()->length($settings['password_min']);
            },
            'login' => function () use ($alias, $settings) {
                return $alias()->length(
                    $settings['login_min'],
                    $settings['login_max']
                );
            },
        ];
    }
    
    public function get($name, $optional = false)
    {
        if (!array_key_exists($name, $this->rules)) {
            throw new InvalidConfigurationException("Validation rule '{$name}' not found.");
        }
        
        $rule = $this->rules[$name]();
        
        return $optional
            ? $this->optional($rule)
            : $rule;
    }

    public function lat($add = '')
    {
        return "/^[\w {$add}]+$/";
    }
    
    public function cyr($add = '')
    {
        return "/^[\w\p{Cyrillic} {$add}]+$/u";
    }
    
    public function optional($rule)
    {
        return v::optional($rule);
    }
}
