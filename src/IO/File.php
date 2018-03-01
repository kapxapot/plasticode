<?php

namespace Plasticode\IO;

class File {
	static public function load($file) {
		return file_get_contents($file);
	}
	
	static public function save($file, $data) {
	    file_put_contents($file, $data);
	}
	
	static public function delete($file) {
		if (file_exists($file)) {
			unlink($file);
		}
	}
	
	static public function cleanUp($mask, $except = null) {
		foreach (glob($mask) as $toDel) {
			if ($toDel != $except) {
				self::delete($toDel);
			}
		}
	}
}
