<?php

namespace Plasticode\Tests\DI;

use PHPUnit\Framework\TestCase;
use Plasticode\Auth\Auth;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Config\TagsConfig;
use Plasticode\Core\Cache;
use Plasticode\Core\Factories\ConsoleLoggerFactory;
use Plasticode\Core\Factories\SessionFactory;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\SessionInterface;
use Plasticode\Core\Linker;
use Plasticode\DI\Autowirer;
use Plasticode\DI\Containers\AutowiringContainer;
use Plasticode\DI\ParamResolvers\UntypedContainerParamResolver;
use Plasticode\Repositories\Idiorm\Core\RepositoryContext;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Settings\SettingsProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouterInterface;
use Slim\Router;
use stdClass;

final class AutowiringTest extends TestCase
{
    private function createContainer(
        Autowirer $autowirer,
        ?array $map = null
    ): AutowiringContainer
    {
        return new AutowiringContainer($autowirer, $map ?? []);
    }

    public function testAutowireFails(): void
    {
        $container = $this->createContainer(
            new Autowirer()
        );

        $this->assertFalse(
            $container->has(SettingsProviderInterface::class)
        );

        $this->expectException(ContainerExceptionInterface::class);

        $container->get(SettingsProviderInterface::class);
    }

    public function testAutowireSettingsProvider(): void
    {
        $container = $this->createContainer(
            new Autowirer(),
            [
                SettingsProviderInterface::class => SettingsProvider::class,
            ]
        );

        $this->assertTrue(
            $container->has(SettingsProviderInterface::class)
        );

        $settingsProvider = $container->get(
            SettingsProviderInterface::class
        );

        $this->assertInstanceOf(SettingsProviderInterface::class, $settingsProvider);
        $this->assertInstanceOf(SettingsProvider::class, $settingsProvider);
    }

    public function testAutowireLinker(): void
    {
        $container = $this->createContainer(
            new Autowirer(),
            [
                LinkerInterface::class => Linker::class,
                RouterInterface::class => Router::class,
                SettingsProviderInterface::class => SettingsProvider::class,
                TagsConfigInterface::class => TagsConfig::class,
            ]
        );

        $this->assertTrue(
            $container->has(LinkerInterface::class)
        );

        $linker = $container->get(
            LinkerInterface::class
        );

        $this->assertInstanceOf(LinkerInterface::class, $linker);
        $this->assertInstanceOf(Linker::class, $linker);

        $settingsProvider = $container->get(
            SettingsProviderInterface::class
        );

        $this->assertInstanceOf(SettingsProviderInterface::class, $settingsProvider);
        $this->assertInstanceOf(SettingsProvider::class, $settingsProvider);

        $router = $container->get(
            RouterInterface::class
        );

        $this->assertInstanceOf(RouterInterface::class, $router);
        $this->assertInstanceOf(Router::class, $router);

        $tagsConfig = $container->get(
            TagsConfigInterface::class
        );

        $this->assertInstanceOf(TagsConfigInterface::class, $tagsConfig);
        $this->assertInstanceOf(TagsConfig::class, $tagsConfig);
    }

    public function testAutowireRepositoryContext(): void
    {
        $container = $this->createContainer(
            new Autowirer(),
            [
                AuthInterface::class => Auth::class,
                CacheInterface::class => Cache::class,
                SessionInterface::class => SessionFactory::class,
                SettingsProviderInterface::class => SettingsProvider::class,
            ]
        );

        // $container->withLogger(
        //     (new ConsoleLoggerFactory())()
        // );

        $this->assertTrue(
            $container->has(RepositoryContext::class)
        );

        $repositoryContext = $container->get(RepositoryContext::class);

        $this->assertInstanceOf(RepositoryContext::class, $repositoryContext);

        $this->assertTrue(
            $container->has(RepositoryContext::class)
        );
    }

    public function testAutowireCallable(): void
    {
        $autowirer = (new Autowirer())->withUntypedParamResolver(
            new UntypedContainerParamResolver()
        );

        $container = $this->createContainer(
            $autowirer,
            [
                'aaa' => SettingsProvider::class,
            ]
        );

        $callable = function ($container) {
            return $container->get('aaa');
        };

        $ccc = $autowirer->autowireCallable($container, $callable);

        $this->assertInstanceOf(SettingsProvider::class, $ccc);
    }

    public function testUntypedContainerResolution(): void
    {
        $autowirer = (new Autowirer())->withUntypedParamResolver(
            new UntypedContainerParamResolver()
        );

        $container = $this->createContainer(
            $autowirer,
            [
                'aaa' => SettingsProvider::class,
                'ccc' => function ($container) {
                    return $container->get('aaa');
                },
            ]
        );

        $ccc = $container->get('ccc');

        $this->assertInstanceOf(SettingsProvider::class, $ccc);
    }

    public function testAliasAndRedirect(): void
    {
        $container = $this->createContainer(
            new Autowirer(),
            [
                'aaa' => 'bbb',
                'bbb' => new stdClass(),
                'ccc' => fn (ContainerInterface $c) => $c->get('bbb'),
            ]
        );

        $ccc = $container->get('ccc');
        $bbb = $container->get('bbb');
        $aaa = $container->get('aaa');

        $this->assertEquals($aaa, $bbb);
        $this->assertEquals($ccc, $bbb);
    }
}
