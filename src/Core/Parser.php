<?php

namespace Plasticode\Core;

use Plasticode\Contained;
use Plasticode\Util\Text;

class Parser extends Contained {
	public function parseCut($text, $url, $full = false) {
		$cut = '[cut]';
		$cutpos = strpos($text, $cut);

		if ($cutpos !== false) {
			if ($full === false) {
				$text = substr($text, 0, $cutpos);
				$text = Text::trimBrs($text);

				$text .= $this->decorator->readMore($url);
			}
			else {
				$text = str_replace($cut, '', $text);
				$text = $this->br2p($text);
			}
			
			$text = $this->cleanMarkup($text);
		}

		return $text;
	}
	
	public function makeAbsolute($text) {
		$siteUrl = $this->linker->abs();

		$text = str_replace("=/", "={$siteUrl}", $text);
		$text = str_replace("=\"/", "=\"{$siteUrl}", $text);
		
		return $text;
	}

	/**
	 * Вырезает из текста теги [tag][/tag].
	 * 
	 * Не используется?
	 */
	public function stripTags($text) {
		return preg_replace('/\[(.*)\](.*)\[\/(.*)\]/U', '\$2', $text);
	}
	
	private function cleanMarkup($text) {
		$replaces = [
			'<p><p' => '<p',
			'</p></p>' => '</p>',
			'<p><div' => '<div',
			'</div></p>' => '</div>',
			'<p><ul>' => '<ul>',
			'</ul></p>' => '</ul>',
			'<p><figure' => '<figure',
			'</figure></p>' => '</figure>',
		];

		foreach ($replaces as $key => $value) {
			$text = preg_replace('#(' . $key . ')#', $value, $text);
		}
		
		return $text;
	}
	
	private function br2p($text) {
		return str_replace('<br/><br/>', '</p><p>', $text);
	}

	public function parse($text) {
		// db replaces
		$text = $this->replaces($text);

		// extend this
		$text = $this->parseMore($text);

		// all brackets are parsed at this point
		// titles
		// !! before linebreaks replacement !!
		$result = $this->parseTitles($text);
		$text = $result['text'];

		// markdown
		// !! before linebreaks replacement !!
		$text = $this->parseMarkdown($text);

		// \n -> br -> p
		$text = str_replace([ "\r\n", "\r", "\n" ], '<br/>', $text);

		// bb [tags]
		$text = $this->parseBrackets($text);
		

		// all text parsed
		$text = preg_replace('#(<br/>){3,}#', '<br/><br/>', $text);
		$text = '<p>' . $this->br2p($text) . '</p>';

		$result['text'] = $this->cleanMarkup($text);
		
		return $result;
	}
	
	/**
	 * Extend this for additional parsing. Double brackets etc.
	 */
	protected function parseMore($text) {
		return $text;
	}

	protected function parseTitles($text) {
		$contents = [];

		$text = Text::processLines($text, function($lines) use (&$contents) {
			$results = [];
			
			$subtitleCount = 0;
			$subtitle2Count = 0;
			
			foreach ($lines as $line) {
				$line = trim($line);
				
				if (strlen($line) > 0) {
					$line = preg_replace_callback(
						'/^((\||#){2,})(.*)$/',
						function($matches) use (&$contents, &$subtitleCount, &$subtitle2Count) {
							$sticks = $matches[1];
							$content = trim($matches[3], ' |');
							
							$withContents = true;
							$label = null;
							
							if (substr($content, -1) == '#') {
								$withContents = false;
								$content = rtrim($content, '#');
							}
			
							if (strlen($sticks) == 2) {
								// subtitle
								if ($withContents === true) {
									$label = ++$subtitleCount;
									$subtitle2Count = 0;
				
									$contents[] = [
										'level' => 1,
										'label' => $label,
										'text' => strip_tags($content),
									];
								}
		 
								$line = $this->decorator->subtitleBlock($content, $label);
							}
							else if (strlen($sticks) == 3) {
								// subtitle2
								if ($withContents === true) {
									$label = $subtitleCount . '_' . ++$subtitle2Count;
				
									$contents[] = [
										'level' => 2,
										'label' => $label,
										'text' => strip_tags($content),
									];
								}
		
								$line = $this->decorator->subtitleBlock($content, $label, 2);
							}
							
							return $line;
						},
						$line
					);
				}
	
				$results[] = $line;
			}
			
			return $results;
		});

		return [ 'text' => $text, 'contents' => $contents ];
	}

	protected function replaces($text) {
		$replaces = $this->db->getReplaces();

		foreach ($replaces as $replace) {
			$text = str_replace($replace['first'], $replace['second'], $text);
		}

		return $text;
	}

	protected function parseUrlBB($text) {
		$newtext = '';
		
		$parts = preg_split('/(\[url.*\].*\[\/url\])/U', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		foreach ($parts as $part) {
			if (preg_match('/\[url(.*)\](.*)\[\/url\]/', $part, $matches)) {
				$attrs = trim($matches[1]);
				$content = $matches[2];
				
				$id = $content;

				if (preg_match('/=(.*)/', $attrs, $matches)) {
					$id = $matches[1];
				}

				if (strlen($id) > 0) {
					$newtext .= $this->decorator->url($id, $content);
				}
				else {
					$newtext .= $content;
				}
			}
			else {
				$newtext .= $part;
			}
		}
		
		return $newtext;
	}

	protected function parseImgBB($text, $tag) {
		$newtext = '';
		
		$parts = preg_split("/(\[{$tag}.*\].*\[\/{$tag}\])/U", $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		foreach ($parts as $part) {
			if (preg_match("/\[{$tag}(.*)\](.*)\[\/{$tag}\]/", $part, $matches)) {
				$attrs = preg_split("/\|/", $matches[1]);
				$source = $matches[2];

				if (strlen($source) > 0) {
					$width = 0;
					$height = 0;
					$alt = null;
					$thumb = null;
					
					foreach ($attrs as $attr) {
						if (is_numeric($attr)) {
							if ($width == 0) {
								$width = $attr;
							}
							else {
								$height = $attr;
							}
						}
						elseif (strpos($attr, 'http') === 0) {
							$thumb = $attr;
						}
						else {
							$alt = $attr;
						}
					}

					$newtext .= $this->decorator->image($tag, $source, $alt, $width, $height, $thumb);
				}
			}
			else {
				$newtext .= $part;
			}
		}
		
		return $newtext;
	}

	protected function parseColorBB($text) {
		$newtext = '';
		
		$parts = preg_split('/(\[color=.*\].*\[\/color\])/U', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		foreach ($parts as $part) {
			if (preg_match('/\[color=(.*)\](.*)\[\/color\]/', $part, $matches)) {
				$color = trim($matches[1]);
				$content = $matches[2];
				
				if (strlen($color) > 0) {
					$newtext .= $this->decorator->colorBlock($color, $content);
				}
				else {
					$newtext .= $content;
				}
			}
			else {
				$newtext .= $part;
			}
		}
		
		return $newtext;
	}

	protected function parseQuoteBB($text, $quotename, callable $renderer, $default = null) {
		$newtext = '';
		
		$parts = preg_split("/(\[{$quotename}[^\[]*\].*\[\/{$quotename}\])/U", $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		foreach ($parts as $part) {
			if (preg_match("/\[{$quotename}([^\[]*)\](.*)\[\/{$quotename}\]/", $part, $matches)) {
				$attrs = preg_split('/\|/', $matches[1], -1, PREG_SPLIT_NO_EMPTY);
				$text = Text::trimBrs($matches[2]);

				if (strlen($text) > 0) {
					$author = null;
					$url = null;

					foreach ($attrs as $attr) {
						if (strpos($attr, 'http') === 0) {
							$url = $attr;
						}
						elseif (strlen($author) == 0) {
							$author = $attr;
						}
						else {
							$date = $attr;
						}
					}

					$newtext .= $renderer($text, $author ?? $default, $url, $date);
				}
			}
			else {
				$newtext .= $part;
			}
		}
		
		return $newtext;
	}

	protected function parseYoutubeBB($text) {
		$newtext = '';
		
		$parts = preg_split('/(\[youtube.*\].*\[\/youtube\])/U', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		foreach ($parts as $part) {
			if (preg_match('/\[youtube(.*)\](.*)\[\/youtube\]/', $part, $matches)) {
				$attrs = preg_split('/\|/', $matches[1]);
				$code = $matches[2];

				if (strlen($code) > 0) {
					$width = 0;
					$height = 0;
					
					if (count($attrs) > 2) {
						$width = $attrs[1];
						$height = $attrs[2];
					}

					$newtext .= $this->decorator->youtubeBlock($code, $width, $height);
				}
			}
			else {
				$newtext .= $part;
			}
		}
		
		return $newtext;
	}

	protected function parseSpoilerBB($text) {
		$newtext = '';
		
		$parts = preg_split('/(\[spoiler.*\].*\[\/spoiler\])/U', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		foreach ($parts as $part) {
			if (preg_match('/\[spoiler(.*)\](.*)\[\/spoiler\]/', $part, $matches)) {
				$attrs = trim($matches[1]);
				$content = Text::trimBrs($matches[2]);

				$label = null;
				if (preg_match('/=(.*)/', $attrs, $matches)) {
					$label = $matches[1];
				}

				$newtext .= $this->decorator->spoilerBlock($content, $label);
			}
			else {
				$newtext .= $part;
			}
		}
		
		return $newtext;
	}

	protected function parseListBB($text) {
		return preg_replace_callback(
			'/\[list(=1)?\](.*)\[\/list\]/Us',
			function($matches) {
				$ordered = strlen($matches[1]) > 0;
				$content = strstr($matches[2], '[*]');
				
				if ($content !== false) {
					$items = preg_split('/\[\*\]/', $content, -1, PREG_SPLIT_NO_EMPTY);
					$result = $this->decorator->list($items, $ordered);
				}

				return $result ?? 'Неверный формат списка!';
			},
			$text
		);
	}

	protected function parseBrackets($text) {
		$text = $this->parseYoutubeBB($text);
		$text = $this->parseColorBB($text);
		$text = $this->parseImgBB($text, 'img');
		$text = $this->parseImgBB($text, 'leftimg');
		$text = $this->parseImgBB($text, 'rightimg');
		$text = $this->parseUrlBB($text);
		$text = $this->parseQuoteBB($text, 'quote', [ $this->decorator, 'quoteBlock' ]);
		$text = $this->parseSpoilerBB($text);
		$text = $this->parseListBB($text);

		return $text;
	}
	
	protected function parseListMD($text) {
		return Text::processLines($text, function($lines) {
			$results = [];
			$list = [];
			$ordered = null;

			$flush = function() use (&$list, &$ordered, &$results) {
				if (count($list) > 0) {
					$results[] = $this->decorator->list($list, $ordered);
					$list = [];
					$ordered = null;
				}
			};
			
			foreach ($lines as $line) {
				if (preg_match('/^(\*|-|\+|(\d+)\.)\s+(.*)$/', trim($line), $matches)) {
					$itemOrdered = strlen($matches[2]) > 0;

					if (count($list) > 0 && $ordered !== $itemOrdered) {
						$flush();
					}
					
					$list[] = $matches[3];
					$ordered = $itemOrdered;
				}
				else {
					$flush();
					$results[] = $line;
				}
			}
			
			$flush();

			return $results;
		});
	}
	
	protected function parseMarkdown($text) {
		$text = $this->parseListMD($text);

		return $text;
	}
}
