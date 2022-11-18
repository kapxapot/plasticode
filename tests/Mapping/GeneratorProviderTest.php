<?php

namespace Plasticode\Tests\Mapping;

use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Controllers\Admin\AdminPageControllerFactory;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Data\Interfaces\ApiInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\MenuGenerator;
use Plasticode\Generators\MenuItemGenerator;
use Plasticode\Generators\RoleGenerator;
use Plasticode\Generators\UserGenerator;
use Plasticode\Middleware\Factories\AccessMiddlewareFactory;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Testing\AbstractProviderTest;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Slim\Interfaces\RouterInterface;

final class GeneratorProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            AccessMiddlewareFactory::class,
            ApiInterface::class,
            AuthInterface::class,
            RouterInterface::class,
            SettingsProviderInterface::class,
            ValidatorInterface::class,
            ViewInterface::class,

            MenuItemRepositoryInterface::class,
            MenuRepositoryInterface::class,
            RoleRepositoryInterface::class,
            UserRepositoryInterface::class,
        ];
    }

    public function testWiring(): void
    {
        $this->check(GeneratorContext::class);
        $this->check(MenuGenerator::class);
        $this->check(MenuItemGenerator::class);
        $this->check(RoleGenerator::class);
        $this->check(UserGenerator::class);

        $this->check(AdminPageControllerFactory::class);
    }
}
