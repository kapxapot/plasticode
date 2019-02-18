<?php

namespace Plasticode\Controllers;

use Plasticode\Contained;
use Plasticode\Core\Core;

class ParserController extends Contained
{
	public function parse($request, $response, $args)
	{
    	$data = $request->getParsedBody();
    	$text = strip_tags($data['text']);
    	
    	$text = $this->parser->justText($text);
    	$text = $this->parser->parseCut($text);

		return Core::json($response, [
		    'text' => $text,
		]);
	}
}
