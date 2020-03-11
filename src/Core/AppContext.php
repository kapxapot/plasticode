<?php

namespace Plasticode\Core;

use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Handlers\NotFoundHandler;
use Plasticode\Interfaces\SettingsProviderInterface;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Psr\Log\LoggerInterface;

class AppContext
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ViewInterface */
    private $view;

    /** @var LoggerInterface */
    private $logger;

    /** @var NotFoundHandler */
    private $notFoundHandler;

    /** @var MenuRepositoryInterface */
    private $menuRepository;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        TranslatorInterface $translator,
        ValidatorInterface $validator,
        ViewInterface $view,
        LoggerInterface $logger,
        NotFoundHandler $notFoundHandler,
        MenuRepositoryInterface $menuRepository
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->view = $view;
        $this->logger = $logger;
        $this->notFoundHandler = $notFoundHandler;
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

    public function notFoundHandler() : NotFoundHandler
    {
        return $this->notFoundHandler;
    }

    public function menuRepository() : MenuRepositoryInterface
    {
        return $this->menuRepository;
    }
}
