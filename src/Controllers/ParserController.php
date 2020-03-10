<?php

namespace Plasticode\Controllers;

use Plasticode\Core\Env;
use Plasticode\Core\Response;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class ParserController
{
    /** @var Env */
    private $env;

    /** @var LoggerInterface */
    private $logger;

    /** @var ParserInterface */
    private $parser;

    /** @var CutParser */
    private $cutParser;

    public function __construct(ContainerInterface $container)
    {
        $this->env = $container->env;
        $this->logger = $container->logger;
        $this->parser = $container->parser;
        $this->cutParser = $container->cutParser;
    }

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
