<?php

namespace Plasticode\Core;

use Plasticode\Exceptions\IApiException;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Util\Text;

class Core {
	public static function json($response, $e, $options = []) {
		$result = is_array($e) ? $e : $e->asArray();
		
		if (isset($options['params']['format'])) {
			$format = $options['params']['format'];
			
			// datatables
			if ($format == 'dt') {
				$wrapper = new \stdClass;
				$wrapper->data = $result;
				
				$result = $wrapper;
			}
		}

		return $response->withJson($result);
	}

	/**
	 * Error, error!
	 * 
	 * @param ContainerInterface $c Slim container
	 * @param object $response
	 * @param \Exception $ex
	 */
	public static function error($c, $response, $ex) {
		$status = 400;

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
		}
		else {
			$msg = $ex->getMessage();
			$msg = $c->translator->translateMessage($msg);
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
