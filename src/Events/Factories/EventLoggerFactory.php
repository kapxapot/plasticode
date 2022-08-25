<?php

namespace Plasticode\Events\Factories;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Plasticode\Config\Config;
use Plasticode\IO\File;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * May return `null` if the event logger is disabled.
 */
class EventLoggerFactory
{
    public function __invoke(
        SettingsProviderInterface $settingsProvider,
        Config $config
    ): ?LoggerInterface
    {
        $logEnabled = $settingsProvider->get('event_logger.enabled', false);

        if (!$logEnabled) {
            return null;
        }

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
