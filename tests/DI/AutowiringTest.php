<?php

namespace Plasticode\Tests\DI;

use PHPUnit\Framework\TestCase;
use Plasticode\Auth\Auth;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Config\TagsConfig;
use Plasticode\Core\Cache;
use Plasticode\Core\Factories\SessionFactory;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\SessionInterface;
use Plasticode\Core\Linker;
use Plasticode\DI\Containers\AutowiredContainer;
use Plasticode\Repositories\Idiorm\Core\RepositoryContext;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Settings\SettingsProvider;
use Slim\Interfaces\RouterInterface;
use Slim\Router;

class AutowiringTest extends TestCase
{
    public function testAutowireSettingsProvider(): void
    {
        $container = new AutowiredContainer(
            [
                SettingsProviderInterface::class => SettingsProvider::class,
            ]
        );

        $settingsProvider = $container->get(
            SettingsProviderInterface::class
        );

        $this->assertInstanceOf(SettingsProviderInterface::class, $settingsProvider);
        $this->assertInstanceOf(SettingsProvider::class, $settingsProvider);
    }

    public function testAutowireLinker(): void
    {
        $container = new AutowiredContainer(
            [
                LinkerInterface::class => Linker::class,
                RouterInterface::class => Router::class,
                SettingsProviderInterface::class => SettingsProvider::class,
                TagsConfigInterface::class => TagsConfig::class,
            ]
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
        $container = new AutowiredContainer(
            [
                AuthInterface::class => Auth::class,
                CacheInterface::class => Cache::class,
                SessionInterface::class => SessionFactory::class,
                SettingsProviderInterface::class => SettingsProvider::class,
            ]
        );

        $this->assertFalse(
            $container->has(RepositoryContext::class)
        );

        $repositoryContext = $container->get(RepositoryContext::class);

        $this->assertInstanceOf(RepositoryContext::class, $repositoryContext);

        $this->assertTrue(
            $container->has(RepositoryContext::class)
        );
    }
}
