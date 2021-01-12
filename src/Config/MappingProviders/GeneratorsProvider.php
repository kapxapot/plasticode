<?php

namespace Plasticode\Config\MappingProviders;

use Plasticode\Config\Config;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Core\GeneratorResolver;
use Plasticode\Generators\MenuGenerator;
use Plasticode\Generators\MenuItemGenerator;
use Plasticode\Generators\RoleGenerator;
use Plasticode\Generators\UserGenerator;
use Plasticode\Interfaces\MappingProviderInterface;
use Plasticode\Middleware\Factories\AccessMiddlewareFactory;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouterInterface;

class GeneratorsProvider implements MappingProviderInterface
{
    public function getMappings(): array
    {
        return [
            GeneratorContext::class =>
                fn (ContainerInterface $c) => new GeneratorContext(
                    $c->get(SettingsProviderInterface::class),
                    $c->get(Config::class),
                    $c->get(RouterInterface::class),
                    $c->get(ApiInterface::class),
                    $c->get(ValidatorInterface::class),
                    $c->get(ValidationRules::class),
                    $c->get(ViewInterface::class),
                    $c->get(AccessMiddlewareFactory::class)
                ),

            GeneratorResolver::class =>
                fn (ContainerInterface $c) => new GeneratorResolver(),

            MenuGenerator::class =>
                fn (ContainerInterface $c) => new MenuGenerator(
                    $c->get(GeneratorContext::class),
                    $c->get(MenuRepositoryInterface::class)
                ),

            MenuItemGenerator::class =>
                fn (ContainerInterface $c) => new MenuItemGenerator(
                    $c->get(GeneratorContext::class),
                    $c->get(MenuRepositoryInterface::class),
                    $c->get(MenuItemRepositoryInterface::class)
                ),

            RoleGenerator::class =>
                fn (ContainerInterface $c) => new RoleGenerator(
                    $c->get(GeneratorContext::class)
                ),

            UserGenerator::class =>
                fn (ContainerInterface $c) => new UserGenerator(
                    $c->get(GeneratorContext::class),
                    $c->get(UserRepositoryInterface::class),
                    $c->get(UserValidation::class)
                ),
        ];
    }
}
