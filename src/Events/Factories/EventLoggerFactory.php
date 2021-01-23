<?php

namespace Plasticode\Events\Factories;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Plasticode\Config\Config;
use Plasticode\IO\File;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class EventLoggerFactory
{
    public function __invoke(ContainerInterface $container): LoggerInterface
    {
        /** @var SettingsProviderInterface */
        $settingsProvider = $container->get(SettingsProviderInterface::class);

        /** @var Config */
        $config = $container->get(Config::class);

        $logger = new Logger(
            $settingsProvider->get('event_logger.name', 'events')
        );

        $path = $settingsProvider->get('event_logger.path');

        if (strlen($path) == 0) {
            return $logger;
        }

        $path = File::combine($config->rootDir(), $path);

        $handler = new StreamHandler($path, Logger::DEBUG);

        $formatter = new LineFormatter(
            null, null, false, true
        );

        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        return $logger;
    }
}
