<?php

namespace Plasticode\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Plasticode\Contained;
use Plasticode\Core\Response;

class ParserController extends Contained
{
    public function parse(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $data = $request->getParsedBody();
        $text = strip_tags($data['text']);
        
        $text = $this->parser->justText($text);
        $text = $this->parser->parseCut($text);

        return Response::json($response, [
            'text' => $text,
        ]);
    }
}
