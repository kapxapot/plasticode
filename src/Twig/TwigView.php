<?php

namespace Plasticode\Twig;

use Plasticode\Core\Interfaces\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

class TwigView implements ViewInterface
{
    /** @var Twig */
    private $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function render(
        ResponseInterface $response,
        string $template,
        array $data = []
    ) : ResponseInterface
    {
        return $this->twig->render($response, $template, $data);
    }
    
    function fetch(string $component, array $data = []) : string
    {
        return $this->twig->fetch($component, $data);
    }
}