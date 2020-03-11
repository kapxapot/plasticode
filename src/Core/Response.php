<?php

namespace Plasticode\Core;

use Plasticode\Exceptions\ValidationException;
use Plasticode\Exceptions\Interfaces\HttpExceptionInterface;
use Plasticode\Exceptions\Interfaces\PropagatedExceptionInterface;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Util\Text;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response as SlimResponse;
use Webmozart\Assert\Assert;

class Response
{
    private const DefaultErrorStatus = 500;

    /**
     * Writes data to response object and returns it.
     *
     * @param SlimResponse $response
     * @param ArrayableInterface|\ORM|array $data
     * @param array $options
     * @return ResponseInterface
     */
    public static function json(
        SlimResponse $response,
        $data,
        array $options = []
    ) : ResponseInterface
    {
        if ($data instanceof ArrayableInterface) {
            $data = $data->toArray();
        } elseif ($data instanceof \ORM) {
            $data = $data->asArray();
        }
        
        Assert::isArray(
            $data,
            'Response::json expects an array, ArrayableInterface or \ORM.'
        );

        if (isset($options['params']['format'])) {
            $format = $options['params']['format'];

            // datatables
            if ($format == 'dt') {
                $wrapper = new \stdClass;
                $wrapper->data = $data;
                
                $data = $wrapper;
            }
        }

        return $response->withJson($data, null, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Writes text to response object and returns it.
     *
     * @param ResponseInterface $response
     * @param string $text
     * @return ResponseInterface
     */
    public static function text(
        ResponseInterface $response,
        string $text
    ) : ResponseInterface
    {
        $response->getBody()->write($text);
        return $response;
    }

    /**
     * Writes error into response and returns it.
     * 
     * @param ContainerInterface $container DI container
     * @param ServerRequestInterface $request HTTP request object
     * @param ResponseInterface $response HTTP response object
     * @param \Exception $ex
     * @return ResponseInterface
     */
    public static function error(
        ContainerInterface $container,
        ServerRequestInterface $request,
        ResponseInterface $response,
        \Exception $ex
    ) : ResponseInterface
    {
        $settings = $container->get('settings');
        $debug = $settings['debug'];

        $jsonRequest = Request::isJson($request);

        // if debug & not json request - just throw the exception
        if ($debug && !$jsonRequest) {
            throw $ex;
        }

        $status = ($ex instanceof HttpExceptionInterface)
            ? $ex->GetErrorCode()
            : self::DefaultErrorStatus;
        
        $msg = null;
        $errors = [];

        // get main message and all errors
        if ($ex instanceof ValidationException) {
            foreach ($ex->errors as $field => $error) {
                $errors[$field] = $error;
                
                if (!$msg) {
                    $msg = $error[0];
                }
            }
        } else {
            $msg = $ex->getMessage();
            $msg = $container->translator->translate($msg);
        }

        // log stack trace if the exception is non-propagated
        if ($settings['log_errors'] && !($ex instanceof PropagatedExceptionInterface)) {
            $container->logger->error("Error: {$msg}");
            
            if (!($ex instanceof ValidationException)) {
                $lines = [];
                
                foreach ($ex->getTrace() as $trace) {
                    $lines[] = "{$trace['file']} ({$trace['line']}), {$trace['class']}{$trace['type']}{$trace['function']}";
                }

                $container->logger->info(Text::fromLines($lines));
            }
        }

        // if not debug - hide non-propagated exception and return general error message
        if (!$debug && !($ex instanceof PropagatedExceptionInterface)) {
            $msg = 'Server error.';
            $errors = null;
        }

        if (!$jsonRequest) {
            // todo: add $errors to the output
            return self::text($response, $msg)
                ->withStatus($status);
        }

        $error = ['error' => true, 'message' => $msg];
        
        if ($errors) {
            $error['errors'] = $errors;
        }
        
        return self::json($response, $error)
            ->withStatus($status);
    }
}
