<?php

namespace Plasticode\Core;

use Plasticode\Exceptions\ValidationException;
use Plasticode\Exceptions\Interfaces\HttpExceptionInterface;
use Plasticode\Exceptions\Interfaces\PropagatedExceptionInterface;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Util\Text;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response as SlimResponse;
use Webmozart\Assert\Assert;

class Response
{
    private const DEFAULT_ERROR_STATUS = 500;
    private const BAD_REQUEST_STATUS = 400;

    /**
     * Writes data to response object and returns it.
     *
     * @param ArrayableInterface|\ORM|array $data
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
     */
    public static function error(
        AppContext $appContext,
        ServerRequestInterface $request,
        ResponseInterface $response,
        \Exception $ex
    ) : ResponseInterface
    {
        $settingsProvider = $appContext->settingsProvider();
        $translator = $appContext->translator();
        $logger = $appContext->logger();

        $debug = $settingsProvider->get('debug');

        $jsonRequest = Request::isJson($request);

        // if debug & not json request - just throw the exception
        if ($debug && !$jsonRequest) {
            throw $ex;
        }

        $status = ($ex instanceof HttpExceptionInterface)
            ? $ex->GetErrorCode()
            : (($ex instanceof ValidationException)
                ? self::BAD_REQUEST_STATUS
                : self::DEFAULT_ERROR_STATUS);

        $msg = null;
        $errors = [];

        // get main message and all errors
        if ($ex instanceof ValidationException) {
            foreach ($ex->errors() as $field => $errorList) {
                $errors[$field] = $errorList;

                if (!$msg) {
                    $msg = $errorList[0];
                }
            }
        } else {
            $msg = $ex->getMessage();
            $msg = $translator->translate($msg);
        }

        // log stack trace if the exception is non-propagated
        if ($settingsProvider->get('log_errors')
            && !($ex instanceof PropagatedExceptionInterface)) {
            $logger->error("Error: {$msg}");

            if (!($ex instanceof ValidationException)) {
                $lines = [];

                foreach ($ex->getTrace() as $trace) {
                    $lines[] = $trace['file'] . ' (' . $trace['line'] . '), ' . $trace['class'] . $trace['type'] . $trace['function'];
                }

                $logger->info(Text::fromLines($lines));
            }
        }

        // if not debug - hide non-propagated exception and return general error message
        if (!$debug && !($ex instanceof PropagatedExceptionInterface)) {
            $msg = 'Server error.';
            $errors = null;
        }

        $error = ['error' => true, 'message' => $msg];

        if ($errors) {
            $error['errors'] = $errors;
        }

        return self::json($response, $error)
            ->withStatus($status);
    }
}
