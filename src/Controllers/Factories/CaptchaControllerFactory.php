<?php

namespace Plasticode\Controllers\Factories;

use Plasticode\Auth\Interfaces\CaptchaInterface;
use Plasticode\Controllers\CaptchaController;
use Plasticode\Core\AppContext;
use Psr\Container\ContainerInterface;

class CaptchaControllerFactory
{
    public function __invoke(ContainerInterface $container): CaptchaController
    {
        return new CaptchaController(
            $container->get(AppContext::class),
            $container->get(CaptchaInterface::class)
        );
    }
}
