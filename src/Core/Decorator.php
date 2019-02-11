<?php

namespace Plasticode\Core;

use Plasticode\Contained;

class Decorator extends Contained
{
	protected function pStart($class = null, $label = null)
	{
		if ($class) {
			$class = " class=\"{$class}\"";
		}

		if ($label) {
			$label = " id=\"{$label}\"";
		}

		return "<p{$class}{$label}>";
	}

	protected function pEnd()
	{
		return '</p>';
	}

	protected function p($text, $class = null, $label = null)
	{
		return $this->pStart($class, $label) . $text . $this->pEnd();
	}

	public function textBlock($text)
	{
		return $this->p($text);
	}

	public function text($text, $class = null)
	{
		if ($class) {
			$class = " class=\"{$class}\"";
		}
		
		return "<span{$class}>{$text}</span>";
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
