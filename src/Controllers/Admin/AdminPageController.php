<?php

namespace Plasticode\Controllers\Admin;

use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Generators\Generic\EntityGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminPageController
{
    private EntityGenerator $generator;
    private ViewInterface $view;

    public function __construct(
        EntityGenerator $generator,
        ViewInterface $view
    )
    {
        $this->generator = $generator;
        $this->view = $view;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $params = $this->generator->getAdminParams($args);

        $action = $request->getQueryParams()['action'] ?? null;
        $id = $request->getQueryParams()['id'] ?? null;

        if ($action) {
            $params['action_onload'] = [
                'action' => $action,
                'id' => $id,
            ];
        }

        if ($this->generator->getAdminArgs()) {
            $params['args'] = $this->generator->getAdminArgs();
        }

        return $this->view->render(
            $response,
            'admin/' . $this->generator->getAdminTemplateName() . '.twig',
            $params
        );
    }
}
