<?php

namespace Plasticode\Controllers\Factories;

use Plasticode\Auth\Interfaces\CaptchaInterface;
use Plasticode\Controllers\AuthController;
use Plasticode\Core\AppContext;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Psr\Container\ContainerInterface;

class AuthControllerFactory
{
    public function __invoke(ContainerInterface $container): AuthController
    {
        return new AuthController(
            $container->get(AppContext::class),
            $container->get(AuthService::class),
            $container->get(CaptchaInterface::class),
            $container->get(UserRepositoryInterface::class),
            $container->get(UserValidation::class)
        );
    }
}
