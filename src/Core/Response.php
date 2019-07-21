<?php

namespace Plasticode\Core;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Plasticode\Collection;
use Plasticode\Exceptions\IApiException;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Util\Text;

class Response
{
    const DEFAULT_ERROR_STATUS = 500;

    /**
     * Writes data to response object and returns it
     *
     * @param ResponseInterface $response
     * @param mixed $data
     * @param array $options
     * @return ResponseInterface
     */
    public static function json(ResponseInterface $response, $data, array $options = []) : ResponseInterface
    {
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }
        elseif ($data instanceof \ORM) {
            $data = $data->asArray();
        }
        
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Core::json expects an array, a Collection or a dbObj.');
        }

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
     * Writes text to response object and returns it
     *
     * @param ResponseInterface $response
     * @param string $text
     * @return ResponseInterface
     */
    public static function text(ResponseInterface $response, string $text) : ResponseInterface
    {
        $response->getBody()->write($text);
        return $response;
    }

    /**
     * Error, error!
     * 
     * Writes error into response and returns it
     * 
     * @param ContainerInterface $container DI container
     * @param ServerRequestInterface $request HTTP request object
     * @param ResponseInterface $response HTTP response object
     * @param \Exception $ex
     * @return ResponseInterface
     */
    public static function error(ContainerInterface $container, ServerRequestInterface $request, ResponseInterface $response, \Exception $ex) : ResponseInterface
    {
        $settings = $container->get('settings');
        $debug = $settings['debug'];

        $jsonRequest = Request::isJson($request);

        // if debug & not json request - just throw the exception
        if ($debug && !$jsonRequest) {
            throw $ex;
        }

        $status = ($ex instanceof IApiException)
            ? $ex->GetErrorCode()
            : self::DEFAULT_ERROR_STATUS;
        
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

        // log stack trace
        if ($settings['log_errors']) {
            $container->logger->error("Error: {$msg}");
            
            if (!($ex instanceof ValidationException)) {
                $lines = [];
                
                foreach ($ex->getTrace() as $trace) {
                    $lines[] = "{$trace['file']} ({$trace['line']}), {$trace['class']}{$trace['type']}{$trace['function']}";
                }

                $container->logger->info(Text::fromLines($lines));
            }
        }

        // if not debug & not json request - hide the exception and show general error
        if (!$debug && !$jsonRequest) {
            return self::text($response, 'Server error.')
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
