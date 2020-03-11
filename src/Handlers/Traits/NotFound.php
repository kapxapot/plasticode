<?php

namespace Plasticode\Handlers\Traits;

use Plasticode\Core\Request;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait NotFound
{
    protected abstract function container() : ContainerInterface;

    protected abstract function translate(string $value) : string;

    protected abstract function render(
        ResponseInterface $response,
        string $template,
        array $data = []
    ) : ResponseInterface;

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        if (Request::isJson($request)) {
            $ex = new NotFoundException();

            return Response::error(
                $this->container(),
                $request,
                $response,
                $ex
            );
        }

        $params = $this->buildParams(
            [
                'params' => [
                    'text' => $this->translate('Page not found or moved.'),
                    'title' => $this->translate('Error 404'),
                    'no_breadcrumbs' => true,
                    'no_disqus' => 1,
                    'no_social' => 1,
                ],
            ]
        );

        return $this->render($response, 'main/generic.twig', $params)
            ->withStatus(404);
    }
}
