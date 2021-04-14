<?php

namespace Plasticode\Tests\Mapping;

use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Data\DbMetadata;
use Plasticode\Data\Idiorm\Providers\RepositoryProvider;
use Plasticode\Hydrators\AuthTokenHydrator;
use Plasticode\Hydrators\MenuHydrator;
use Plasticode\Hydrators\MenuItemHydrator;
use Plasticode\Hydrators\TagHydrator;
use Plasticode\Hydrators\UserHydrator;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
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
use Plasticode\Testing\AbstractProviderTest;

final class IdiormRepositoryProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            Access::class,
            AuthInterface::class,
            CacheInterface::class,
            DbMetadata::class,
            LinkerInterface::class,
        ];
    }

    protected function getProvider(): ?MappingProviderInterface
    {
        return new RepositoryProvider();
    }

    public function testWiring(): void
    {
        $this->check(RepositoryContext::class);

        $this->check(AuthTokenHydrator::class);
        $this->check(MenuItemHydrator::class);
        $this->check(MenuHydrator::class);
        $this->check(TagHydrator::class);
        $this->check(UserHydrator::class);

        $this->check(AuthTokenRepositoryInterface::class, AuthTokenRepository::class);
        $this->check(MenuItemRepositoryInterface::class, MenuItemRepository::class);
        $this->check(MenuRepositoryInterface::class, MenuRepository::class);
        $this->check(RoleRepositoryInterface::class, RoleRepository::class);
        $this->check(TagRepositoryInterface::class, TagRepository::class);
        $this->check(UserRepositoryInterface::class, UserRepository::class);
    }
}
