<?php

namespace Plasticode\Core;

use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Handlers\NotFoundHandler;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Psr\Log\LoggerInterface;

class AppContext
{
    private SettingsProviderInterface $settingsProvider;
    private TranslatorInterface $translator;
    private ValidatorInterface $validator;
    private ViewInterface $view;
    private LoggerInterface $logger;
    private MenuRepositoryInterface $menuRepository;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        TranslatorInterface $translator,
        ValidatorInterface $validator,
        ViewInterface $view,
        LoggerInterface $logger,
        MenuRepositoryInterface $menuRepository
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->view = $view;
        $this->logger = $logger;
        $this->menuRepository = $menuRepository;
    }

    public function settingsProvider() : SettingsProviderInterface
    {
        return $this->settingsProvider;
    }

    public function translator() : TranslatorInterface
    {
        return $this->translator;
    }

    public function validator() : ValidatorInterface
    {
        return $this->validator;
    }

    public function view() : ViewInterface
    {
        return $this->view;
    }

    public function logger() : LoggerInterface
    {
        return $this->logger;
    }

    public function menuRepository() : MenuRepositoryInterface
    {
        return $this->menuRepository;
    }
}
