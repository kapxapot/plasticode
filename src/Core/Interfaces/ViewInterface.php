<?php

namespace Plasticode\Core\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface ViewInterface
{
    function render(
        ResponseInterface $response,
        string $template,
        array $data = []
    ) : ResponseInterface;
    
    function fetch(string $component, array $data = []) : string;
}
