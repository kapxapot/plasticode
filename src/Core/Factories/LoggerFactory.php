<?php

namespace Plasticode\Core\Factories;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Config\Config;
use Plasticode\IO\File;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class LoggerFactory
{
    public function __invoke(ContainerInterface $container): LoggerInterface
    {
        /** @var SettingsProviderInterface */
        $settingsProvider = $container->get(SettingsProviderInterface::class);

        /** @var AuthInterface */
        $auth = $container->get(AuthInterface::class);

        /** @var Config */
        $config = $container->get(Config::class);

        $logger = new Logger(
            $settingsProvider->get('logger.name', 'plasticode')
        );

        $logger->pushProcessor(
            function ($record) use ($auth) {
                $user = $auth->getUser();

                if ($user) {
                    $record['extra']['user'] = $user->toString();
                }

                $token = $auth->getToken();

                if ($token) {
                    $record['extra']['token'] = $token->toString();
                }

                return $record;
            }
        );

        $path = $settingsProvider->get('logger.path');

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
