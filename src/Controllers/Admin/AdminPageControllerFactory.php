<?php

namespace Plasticode\Controllers\Admin;

use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Generators\Generic\EntityGenerator;

class AdminPageControllerFactory
{
    private ViewInterface $view;

    public function __construct(
        ViewInterface $view
    )
    {
        $this->view = $view;
    }

    public function __invoke(
        EntityGenerator $generator
    ): AdminPageController
    {
        return new AdminPageController($generator, $this->view);
    }
}
