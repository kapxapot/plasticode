<?php

namespace Plasticode\Generators\Core;

use Plasticode\Config\Config;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Data\Interfaces\ApiInterface;
use Plasticode\Middleware\Factories\AccessMiddlewareFactory;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Slim\Interfaces\RouterInterface;

class GeneratorContext
{
    private SettingsProviderInterface $settingsProvider;
    private Config $config;
    private RouterInterface $router;
    private ApiInterface $api;
    private ValidatorInterface $validator;
    private ValidationRules $validationRules;
    private ViewInterface $view;
    private AccessMiddlewareFactory $accessMiddlewareFactory;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        Config $config,
        RouterInterface $router,
        ApiInterface $api,
        ValidatorInterface $validator,
        ValidationRules $validationRules,
        ViewInterface $view,
        AccessMiddlewareFactory $accessMiddlewareFactory
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->config = $config;
        $this->router = $router;
        $this->api = $api;
        $this->validator = $validator;
        $this->validationRules = $validationRules;
        $this->view = $view;
        $this->accessMiddlewareFactory = $accessMiddlewareFactory;
    }

    public function settingsProvider(): SettingsProviderInterface
    {
        return $this->settingsProvider;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function router(): RouterInterface
    {
        return $this->router;
    }

    public function api(): ApiInterface
    {
        return $this->api;
    }

    public function validator(): ValidatorInterface
    {
        return $this->validator;
    }

    public function validationRules(): ValidationRules
    {
        return $this->validationRules;
    }

    public function view(): ViewInterface
    {
        return $this->view;
    }

    public function accessMiddlewareFactory(): AccessMiddlewareFactory
    {
        return $this->accessMiddlewareFactory;
    }
}
