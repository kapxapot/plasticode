<?php

namespace Plasticode\Controllers\Factories;

use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Controllers\PasswordController;
use Plasticode\Core\AppContext;
use Plasticode\Models\Validation\PasswordValidation;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Psr\Container\ContainerInterface;

class PasswordControllerFactory
{
    public function __invoke(ContainerInterface $container): PasswordController
    {
        return new PasswordController(
            $container->get(AppContext::class),
            $container->get(AuthInterface::class),
            $container->get(UserRepositoryInterface::class),
            $container->get(PasswordValidation::class)
        );
    }
}
