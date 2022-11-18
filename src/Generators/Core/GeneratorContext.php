<?php

namespace Plasticode\Generators\Core;

use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Config\Config;
use Plasticode\Data\Interfaces\ApiInterface;
use Plasticode\Middleware\Factories\AccessMiddlewareFactory;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Slim\Interfaces\RouterInterface;

class GeneratorContext
{
    private Access $access;
    private AccessMiddlewareFactory $accessMiddlewareFactory;
    private ApiInterface $api;
    private AuthInterface $auth;
    private Config $config;
    private RouterInterface $router;
    private SettingsProviderInterface $settingsProvider;
    private ValidationRules $validationRules;
    private ValidatorInterface $validator;

    public function __construct(
        Access $access,
        AccessMiddlewareFactory $accessMiddlewareFactory,
        ApiInterface $api,
        AuthInterface $auth,
        Config $config,
        RouterInterface $router,
        SettingsProviderInterface $settingsProvider,
        ValidationRules $validationRules,
        ValidatorInterface $validator
    )
    {
        $this->access = $access;
        $this->accessMiddlewareFactory = $accessMiddlewareFactory;
        $this->api = $api;
        $this->auth = $auth;
        $this->config = $config;
        $this->router = $router;
        $this->settingsProvider = $settingsProvider;
        $this->validationRules = $validationRules;
        $this->validator = $validator;
    }

    public function access(): Access
    {
        return $this->access;
    }

    public function accessMiddlewareFactory(): AccessMiddlewareFactory
    {
        return $this->accessMiddlewareFactory;
    }

    public function api(): ApiInterface
    {
        return $this->api;
    }

    public function auth(): AuthInterface
    {
        return $this->auth;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function router(): RouterInterface
    {
        return $this->router;
    }

    public function settingsProvider(): SettingsProviderInterface
    {
        return $this->settingsProvider;
    }

    public function validationRules(): ValidationRules
    {
        return $this->validationRules;
    }

    public function validator(): ValidatorInterface
    {
        return $this->validator;
    }
}
