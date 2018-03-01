<?php

namespace Plasticode\Core;

class Translator {
	private $dictionaries;

	public function __construct($dictionaries) {
		$this->dictionaries = $dictionaries;
	}
	
	public function translateMessage($message) {
		return $this->translate('messages', $message);
	}
	
	public function translateField($field) {
		return $this->translate('fields', $field);
	}
	
	private function translate($dictionaryName, $value) {
		if (isset($this->dictionaries[$dictionaryName])) {
			$dictionary = $this->dictionaries[$dictionaryName];
			
			if (isset($dictionary[$value])) {
				$result = $dictionary[$value];
			}
		}
		
		return $result ?? $value;
	}
}
