<?php

namespace Plasticode\Auth;

use Plasticode\Contained;
use Plasticode\Util\Date;
use Plasticode\Util\Numbers;

class Captcha extends Contained {
	private $numbers;
	private $ttl;
	
	/**
	 * @var array $fuckUpReplacements Init this array in constructor, otherwise your captcha will be not captcha.
	 */
	private $fuckUpReplacements = [];

	/**
	 * Creates Captcha instance.
	 * 
	 * @param ContainerInterface $container
	 * @param array $replacements Your custom replacement rules. You MUST provide them.
	 * @param int $ttl Time to live in minutes
	 */
	public function __construct($container, $replacements = [], $ttl = 10) {
		parent::__construct($container);
		
		$this->numbers = new Numbers;
		$this->ttl = $ttl;
		$this->fuckUpReplacements = $replacements;
	}

	private function fuckUp($str) {
		foreach ($this->fuckUpReplacements as $key => $reps) {
			$rep = $reps[mt_rand(0, count($reps) - 1)];
			$str = str_replace($key, $rep, $str);
		}

		return $str;
	}

	public function generate($length, $save = false) {
		$num = $this->numbers->generate($length);
		$string = $this->numbers->toString($num);
		
		$fuckedUpString = implode('', array_map(function($value) {
		    return $this->fuckUp($value);
		}, explode(' ', $string)));

		$result = [
			'number' => $num,
			'string' => $string,
			'captcha' => $fuckedUpString
		];
		
		if ($save) {
			$this->save($result);
		}
		
		return $result;
	}
	
	private function save($captcha) {
		$captcha['expires_at'] = Date::generateExpirationTime($this->ttl);

		$this->session->set('captcha', $captcha);
	}
	
	/**
	 * Burn after read
	 */
	private function load() {
		return $this->session->getAndDelete('captcha');
	}
	
	public function validate($number) {
		$captcha = $this->load();

		return $captcha
			&& is_numeric($number)
			&& $captcha['number'] == $number
			&& strtotime($captcha['expires_at']) >= time();
	}
}
