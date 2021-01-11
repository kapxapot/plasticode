<?php

namespace Plasticode\Config\MappingProviders;

use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Interfaces\MappingProviderInterface;
use Plasticode\Models\Validation\PasswordValidation;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Plasticode\Validation\Validator;
use Psr\Container\ContainerInterface;

class ValidatorsProvider implements MappingProviderInterface
{
    public function getMappings(): array
    {
        return [
            ValidatorInterface::class =>
                fn (ContainerInterface $c) => new Validator(
                    $c->get(TranslatorInterface::class)
                ),

            ValidationRules::class =>
                fn (ContainerInterface $c) => new ValidationRules(
                    $c->get(SettingsProviderInterface::class)
                ),

            PasswordValidation::class =>
                fn (ContainerInterface $c) => new PasswordValidation(
                    $c->get(ValidationRules::class)
                ),

            UserValidation::class =>
                fn (ContainerInterface $c) => new UserValidation(
                    $c->get(ValidationRules::class),
                    $c->get(UserRepositoryInterface::class)
                ),
        ];
    }
}
