<?php

namespace Plasticode\Handlers;

use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Handlers\Traits\NotFound;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class NotFoundHandler
{
    use NotFound;

    /** @var ContainerInterface */
    private $container;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ViewInterface */
    private $view;

    public function __construct(
        ContainerInterface $container,
        TranslatorInterface $translator,
        ViewInterface $view
    )
    {
        $this->container = $container;
        $this->translator = $translator;
        $this->view = $view;
    }

    protected function container() : ContainerInterface
    {
        return $this->container;
    }

    protected function translate(string $value) : string
    {
        return $this->translator->translate($value);
    }

    protected function render(
        ResponseInterface $response,
        string $template,
        array $data = []
    ) : ResponseInterface
    {
        return $this->view->render($response, $template, $data);
    }
}
