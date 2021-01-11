<?php

namespace Plasticode\Core\Factories;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Plasticode\Auth\Auth;
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

        /** @var Auth */
        $auth = $container->get(Auth::class);

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
        $path = File::absolutePath($config->rootDir(), $path);

        $handler = new StreamHandler(
            $path ?? '',
            $path ? Logger::DEBUG : 999
        );

        $formatter = new LineFormatter(
            null, null, false, true
        );

        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        return $logger;
    }
}