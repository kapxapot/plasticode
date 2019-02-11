<?php

namespace Plasticode\Core;

class Renderer
{
    protected $view;
    
    public function __construct($view)
    {
        $this->view = $view;
    }
    
	public function text($text, $style = null, $id = null)
	{
		return $this->component('text', [
		    'text' => $text,
		    'style' => $style,
		    'id' => $id,
		]);
	}

	public function next()
	{
	    return $this->component('next');
	}
	
	public function prev()
	{
	    return $this->component('prev');
	}

	public function component($name, $data = null)
	{
	    return $this->view->fetch('components/spaceless.twig', [
	        'name' => $name,
	        'data' => $data ?? [],
        ]);
	}
}
