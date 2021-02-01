<?php

namespace Plasticode\Data\Idiorm\Providers;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\External\Gravatar;
use Plasticode\Hydrators\AuthTokenHydrator;
use Plasticode\Hydrators\MenuHydrator;
use Plasticode\Hydrators\MenuItemHydrator;
use Plasticode\Hydrators\TagHydrator;
use Plasticode\Hydrators\UserHydrator;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\ObjectProxy;
use Plasticode\Repositories\Idiorm\AuthTokenRepository;
use Plasticode\Repositories\Idiorm\Core\RepositoryContext;
use Plasticode\Repositories\Idiorm\MenuItemRepository;
use Plasticode\Repositories\Idiorm\MenuRepository;
use Plasticode\Repositories\Idiorm\RoleRepository;
use Plasticode\Repositories\Idiorm\TagRepository;
use Plasticode\Repositories\Idiorm\UserRepository;
use Plasticode\Repositories\Interfaces\AuthTokenRepositoryInterface;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Psr\Container\ContainerInterface;

class RepositoryProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            AuthTokenRepositoryInterface::class =>
                fn (ContainerInterface $c) => new AuthTokenRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new AuthTokenHydrator(
                            $c->get(UserRepositoryInterface::class)
                        )
                    )
                ),

            MenuItemRepositoryInterface::class =>
                fn (ContainerInterface $c) => new MenuItemRepository(
                    $c->get(RepositoryContext::class),
                    new MenuItemHydrator(
                        $c->get(LinkerInterface::class)
                    )
                ),

            MenuRepositoryInterface::class =>
                fn (ContainerInterface $c) => new MenuRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new MenuHydrator(
                            $c->get(MenuItemRepositoryInterface::class),
                            $c->get(LinkerInterface::class)
                        )
                    )
                ),

            RoleRepositoryInterface::class =>
                fn (ContainerInterface $c) => new RoleRepository(
                    $c->get(RepositoryContext::class)
                ),

            TagRepositoryInterface::class =>
                fn (ContainerInterface $c) => new TagRepository(
                    $c->get(RepositoryContext::class),
                    new TagHydrator(
                        $c->get(LinkerInterface::class)
                    )
                ),

            UserRepositoryInterface::class =>
                fn (ContainerInterface $c) => new UserRepository(
                    $c->get(RepositoryContext::class),
                    new ObjectProxy(
                        fn () => new UserHydrator(
                            $c->get(RoleRepositoryInterface::class),
                            $c->get(LinkerInterface::class),
                            $c->get(Gravatar::class)
                        )
                    )
                ),
        ];
    }
}
