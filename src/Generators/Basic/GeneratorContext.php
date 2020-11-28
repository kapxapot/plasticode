<?php

namespace Plasticode\Generators\Basic;

use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Data\Interfaces\ApiInterface;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Slim\Interfaces\RouterInterface;

class GeneratorContext
{
    private SettingsProviderInterface $settingsProvider;
    private RouterInterface $router;
    private ApiInterface $api;
    private ValidatorInterface $validator;
    private ValidationRules $validationRules;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        RouterInterface $router,
        ApiInterface $api,
        ValidatorInterface $validator,
        ValidationRules $validationRules
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->router = $router;
        $this->api = $api;
        $this->validator = $validator;
        $this->validationRules = $validationRules;
    }

    public function settingsProvider() : SettingsProviderInterface
    {
        return $this->settingsProvider;
    }

    public function router() : RouterInterface
    {
        return $this->router;
    }

    public function api() : ApiInterface
    {
        return $this->api;
    }

    public function validator() : ValidatorInterface
    {
        return $this->validator;
    }

    public function validationRules() : ValidationRules
    {
        return $this->validationRules;
    }
}
