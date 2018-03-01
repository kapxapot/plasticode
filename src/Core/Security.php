<?php

namespace Plasticode\Core;

class Security {
	static public function verifyPassword($password, $hashedPassword) {
		return password_verify($password, $hashedPassword);
	}

	static public function encodePassword($password) {
		return password_hash($password, PASSWORD_DEFAULT);
	}
	
	static public function rehashPasswordNeeded($password) {
		return password_needs_rehash($password, PASSWORD_DEFAULT);
	}
	
	static public function generateToken($length = 16) {
		return bin2hex(openssl_random_pseudo_bytes($length));
	}
}
