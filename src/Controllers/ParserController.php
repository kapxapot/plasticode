<?php

namespace Plasticode\Controllers;

use Exception;
use Plasticode\Core\Env;
use Plasticode\Core\Response;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class ParserController
{
    private Env $env;
    private LoggerInterface $logger;
    private ParserInterface $parser;
    private CutParser $cutParser;

    public function __construct(
        Env $env,
        LoggerInterface $logger,
        ParserInterface $parser,
        CutParser $cutParser
    )
    {
        $this->env = $env;
        $this->logger = $logger;
        $this->parser = $parser;
        $this->cutParser = $cutParser;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        $data = $request->getParsedBody();
        $text = strip_tags($data['text'] ?? '');

        try
        {
            $context = $this->parser->parse($text);
            $context = $this->parser->renderLinks($context);

            $text = $this->cutParser->full($context->text);
        } catch (Exception $ex) {
            $debugMessage = 'Parsing error: ' . $ex->getMessage();

            $text = $this->env->isDev()
                ? $debugMessage
                : 'Parsing error.';

            $this->logger->error($debugMessage);
        }

        return Response::json($response, ['text' => $text]);
    }
}
