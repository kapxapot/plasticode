<?php

namespace Plasticode\Mapping\Providers;

use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Repositories\Interfaces\AuthTokenRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Services\AuthService;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Container\ContainerInterface;

class ServiceProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            AuthService::class =>
                fn (ContainerInterface $c) => new AuthService(
                    $c->get(AuthInterface::class),
                    $c->get(SettingsProviderInterface::class),
                    $c->get(AuthTokenRepositoryInterface::class),
                    $c->get(UserRepositoryInterface::class)
                ),
        ];
    }
}
