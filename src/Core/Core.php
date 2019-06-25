<?php

namespace Plasticode\Core;

use Respect\Validation\Validator as v;

use Plasticode\Collection;
use Plasticode\Exceptions\IApiException;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Models\Model;
use Plasticode\Util\Text;

class Core
{
    public static function bootstrap($container, $settings)
    {
        foreach ($settings as $key => $setting) {
            $container[$key] = $setting;
        }
            
        v::with('Plasticode\\Validation\\Rules\\');
        v::with('App\\Validation\\Rules\\'); // refactor this, this shouldn't be here
        
        Model::init($container);
    }

    public static function isJsonRequest($request) : bool
    {
        $contentType = $request->getContentType();
        return (strpos($contentType, 'application/json') !== false);
    }
    
    public static function json($response, $data, $options = [])
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
     * Error, error!
     * 
     * @param ContainerInterface $c Slim container
     * @param object $response
     * @param \Exception $ex
     */
    public static function error($c, $response, $ex, $debug = false)
    {
        $status = 500;

        if ($ex instanceof IApiException) {
            $status = $ex->GetErrorCode();
        }
        
        $msg = null;
        $errors = [];

        if ($ex instanceof ValidationException) {
            foreach ($ex->errors as $field => $error) {
                $errors[$field] = $error;
                
                if (!$msg) {
                    $msg = $error[0];
                }
            }
        } else {
            //if ($debug) {
                $msg = $ex->getMessage();
            /*} else {
                $msg = "Server error.";
            }*/
            
            $msg = $c->translator->translate($msg);
        }
        
        $settings = $c->get('settings');

        if ($settings['log_errors']) {
            $c->logger->error("Error: {$msg}");
            
            if (!($ex instanceof ValidationException)) {
                $lines = [];
                
                foreach ($ex->getTrace() as $trace) {
                    /*$args = implode(', ', $trace['args']);
                    var_dump($args);*/
                    $lines[] = "{$trace['file']} ({$trace['line']}), {$trace['class']}{$trace['type']}{$trace['function']}";
                }

                $c->logger->info(Text::fromLines($lines));
            }
        }

        $error = [ 'error' => true, 'message' => $msg ];
        
        if ($errors) {
            $error['errors'] = $errors;
        }
        
        return self::json($response, $error)->withStatus($status);
    }
}
