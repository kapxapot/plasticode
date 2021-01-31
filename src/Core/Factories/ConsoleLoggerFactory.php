<?php

namespace Plasticode\Core\Factories;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class ConsoleLoggerFactory
{
    public function __invoke(): LoggerInterface
    {
        return (new Logger('console'))
            ->pushHandler(new StreamHandler('php://stdout'));
    }
}
