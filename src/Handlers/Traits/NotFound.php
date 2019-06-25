<?php

namespace Plasticode\Handlers\Traits;

use Plasticode\Core\Core;
use Plasticode\Exceptions\NotFoundException;

trait NotFound
{
    public function __invoke($request, $response)
    {
        if (Core::isJsonRequest($request)) {
            return Core::error($this->container, $response, new NotFoundException());
        }

        $params = $this->buildParams([
            'params' => [
                'text' => $this->translate('Page not found or moved somewhere we don\'t know where.'),
                'title' => $this->translate('Error 404'),
                'no_breadcrumbs' => true,
                'no_disqus' => 1,
                'no_social' => 1,
            ],
        ]);

        return $this->view->render($response, 'main/generic.twig', $params)
            ->withStatus(404);
    }
}
