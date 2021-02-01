<?php

namespace Plasticode\Tests\Mapping;

use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Handlers\ErrorHandler;
use Plasticode\Handlers\Interfaces\ErrorHandlerInterface;
use Plasticode\Handlers\Interfaces\NotAllowedHandlerInterface;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Plasticode\Handlers\NotAllowedHandler;
use Plasticode\Handlers\NotFoundHandler;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Plasticode\Mapping\Providers\SlimProvider;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouterInterface;

final class SlimProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            LoggerInterface::class,
            TranslatorInterface::class,
            SettingsProviderInterface::class,
            ValidatorInterface::class,
            ViewInterface::class,

            MenuRepositoryInterface::class,
        ];
    }

    protected function getProvider(): ?MappingProviderInterface
    {
        return new SlimProvider();
    }

    public function testWiring(): void
    {
        // $this->container->withLogger(
        //     (new ConsoleLoggerFactory())()
        // );

        $this->check('errorHandler', ErrorHandler::class);
        $this->check(ErrorHandlerInterface::class, ErrorHandler::class);

        $this->check('notAllowedHandler', NotAllowedHandler::class);
        $this->check(NotAllowedHandlerInterface::class, NotAllowedHandler::class);

        $this->check('notFoundHandler', NotFoundHandler::class);
        $this->check(NotFoundHandlerInterface::class, NotFoundHandler::class);

        $this->container->set('router', $this->prophesize(RouterInterface::class)->reveal());
        $this->container->set('request', $this->prophesize(ServerRequestInterface::class)->reveal());

        $this->check(RouterInterface::class);
        $this->check(ServerRequestInterface::class);
    }
}
