<?php

namespace Plasticode\Data\Idiorm\Providers;

use ORM;
use PDO;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Container\ContainerInterface;

class DatabaseProvider extends MappingProvider
{
    public function boot(ContainerInterface $container): void
    {
        parent::boot($container);

        /** @var SettingsProviderInterface */
        $settingsProvider = $container->get(SettingsProviderInterface::class);

        $dbs = $settingsProvider->get('db');

        $adapter = $dbs['adapter'] ?? null;

        if ($adapter !== 'mysql') {
            throw new InvalidConfigurationException(
                'The only supported DB adapter is MySQL, sorry.'
            );
        }

        // init Idiorm

        ORM::configure(
            'mysql:host=' . $dbs['host'] . ';dbname=' . $dbs['database']
        );

        $config = [
            'username' => $dbs['user'],
            'password' => $dbs['password'] ?? '',
            'driver_options' => [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
            ]
        ];

        $port = $dbs['port'] ?? null;

        if ($port > 0) {
            $config['port'] = $port;
        }

        ORM::configure($config);
    }
}
