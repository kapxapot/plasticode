<?php

namespace Plasticode\Tests\Mapping;

use PHPUnit\Framework\TestCase;
use Plasticode\Auth\Access;
use Plasticode\Config\Config;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Config\TagsConfig;
use Plasticode\Core\Cache;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Core\Pagination;
use Plasticode\DI\Autowirer;
use Plasticode\DI\Containers\AutowiringContainer;
use Plasticode\Mapping\Aggregators\WritableMappingAggregator;
use Plasticode\Mapping\Providers\CoreProvider;
use Plasticode\Middleware\Factories\AccessMiddlewareFactory;
use Plasticode\Repositories\Interfaces\AuthTokenRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Settings\SettingsProvider;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouterInterface;
use Slim\Router;

final class CoreProviderTest extends TestCase
{
    use ProphecyTrait;

    public function testWiring(): void
    {
        $container = new AutowiringContainer(
            new Autowirer(),
            [
                RouterInterface::class =>
                    fn () => $this->prophesize(RouterInterface::class)->reveal(),

                ServerRequestInterface::class =>
                    fn () => $this->prophesize(ServerRequestInterface::class)->reveal(),

                AuthTokenRepositoryInterface::class =>
                    fn () => $this->prophesize(AuthTokenRepositoryInterface::class)->reveal(),

                UserRepositoryInterface::class =>
                    fn () => $this->prophesize(UserRepositoryInterface::class)->reveal(),
            ]
        );

        $bootstrap = new WritableMappingAggregator($container);

        $bootstrap->register(
            new CoreProvider(
                [
                    'root_dir' => '',
                    'view' => [
                        'templates_path' => '',
                        'cache_path' => '',
                    ]
                ]
            )
        );

        $bootstrap->boot();

        $this->assertInstanceOf(Access::class, $container->get(Access::class));

        $this->assertInstanceOf(
            AccessMiddlewareFactory::class,
            $container->get(AccessMiddlewareFactory::class)
        );

        $this->assertInstanceOf(Cache::class, $container->get(CacheInterface::class));
        $this->assertInstanceOf(Config::class, $container->get(Config::class));
        $this->assertInstanceOf(Pagination::class, $container->get(Pagination::class));

        $this->assertInstanceOf(
            SettingsProvider::class,
            $container->get(SettingsProviderInterface::class)
        );

        $this->assertInstanceOf(
            TagsConfig::class,
            $container->get(TagsConfigInterface::class)
        );
    }
}
