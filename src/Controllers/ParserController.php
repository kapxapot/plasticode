<?php

namespace Plasticode\Controllers;

use Plasticode\Contained;
use Plasticode\Core\Response;
use Plasticode\Parsing\Parsers\CompositeParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @property CompositeParser $parser
 */
class ParserController extends Contained
{
    public function parse(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $data = $request->getParsedBody();
        $text = strip_tags($data['text']);
        
        $context = $this->parser->parse($text);
        $context = $this->parser->renderLinks($context);

        $text = $this->cutParser->full($context->text);

        return Response::json($response, ['text' => $text]);
    }
}
