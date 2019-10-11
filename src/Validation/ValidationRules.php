<?php

namespace Plasticode\Validation;

use Plasticode\Contained;
use Plasticode\Exceptions\InvalidConfigurationException;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ValidationRules extends Contained
{
    /**
     * Rules
     *
     * @var array
     */
    private $rules;
    
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        
        $this->rules = $this->buildRules();
    }
    
    private function buildRules() : array
    {
        $settings = $this->getSettings();
        
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
            'password' => function () use ($settings) {
                return Validator::noWhitespace()->length($settings['password_min']);
            },
            'login' => function () use ($alias, $settings) {
                return $alias()->length(
                    $settings['login_min'],
                    $settings['login_max']
                );
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
