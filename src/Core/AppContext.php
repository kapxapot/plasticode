<?php

namespace Plasticode\Core;

use Plasticode\Collections\MenuCollection;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Psr\Log\LoggerInterface;

class AppContext
{
    protected SettingsProviderInterface $settingsProvider;
    protected TranslatorInterface $translator;
    protected ValidatorInterface $validator;
    protected ViewInterface $view;
    protected LoggerInterface $logger;

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

    public function getMenus() : MenuCollection
    {
        return $this->menuRepository->getAll();
    }
}
