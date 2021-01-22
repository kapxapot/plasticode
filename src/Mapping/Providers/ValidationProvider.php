<?php

namespace Plasticode\Mapping\Providers;

use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Models\Validation\PasswordValidation;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Plasticode\Validation\Validator;
use Psr\Container\ContainerInterface;

class ValidationProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            PasswordValidation::class =>
                fn (ContainerInterface $c) => new PasswordValidation(
                    $c->get(ValidationRules::class)
                ),

            ValidatorInterface::class =>
                fn (ContainerInterface $c) => new Validator(
                    $c->get(TranslatorInterface::class)
                ),

            ValidationRules::class =>
                fn (ContainerInterface $c) => new ValidationRules(
                    $c->get(SettingsProviderInterface::class)
                ),

            UserValidation::class =>
                fn (ContainerInterface $c) => new UserValidation(
                    $c->get(ValidationRules::class),
                    $c->get(UserRepositoryInterface::class)
                ),
        ];
    }
}
