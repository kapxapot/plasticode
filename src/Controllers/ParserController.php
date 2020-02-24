<?php

namespace Plasticode\Controllers;

use Plasticode\Contained;
use Plasticode\Core\Env;
use Plasticode\Core\Response;
use Plasticode\Parsing\Parsers\CompositeParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * @property CompositeParser $parser
 * @property CutParser $cutParser
 * @property Env $env
 * @property LoggerInterface $logger
 */
class ParserController extends Contained
{
    public function parse(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $data = $request->getParsedBody();
        $text = strip_tags($data['text'] ?? '');
        
        try
        {
            $context = $this->parser->parse($text);
            $context = $this->parser->renderLinks($context);
    
            $text = $this->cutParser->full($context->text);
        } catch (\Exception $ex) {
            $debugMessage = 'Parsing error: ' . $ex->getMessage();

            $text = $this->env->isDev()
                ? $debugMessage
                : 'Parsing error.';

            $this->logger->error($debugMessage);
        }

        return Response::json($response, ['text' => $text]);
    }
}
