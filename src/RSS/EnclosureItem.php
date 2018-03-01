<?php

namespace Plasticode\RSS;

class EnclosureItem extends HtmlDescribable {
	/*
	* 
	* core variables
	*
	**/
	var $url,$length,$type;
	
	/*
	* For use with another extension like Yahoo mRSS
	* Warning :
	* These variables might not show up in 
	* later release / not finalize yet!
	*
	*/
	var $width, $height, $title, $description, $keywords, $thumburl;
	
	var $additionalElements = [];
}
