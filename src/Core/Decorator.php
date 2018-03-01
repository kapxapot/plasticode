<?php

namespace Plasticode\Core;

use Plasticode\Contained;

class Decorator extends Contained {
	protected function p($text, $class = null, $label = null) {
		return $this->pStart($class, $label) . $text . $this->pEnd();
	}

	protected function pStart($class = null, $label = null) {
		if ($class) {
			$class = " class=\"{$class}\"";
		}

		if ($label) {
			$label = " id=\"{$label}\"";
		}

		return "<p{$class}{$label}>";
	}

	protected function pEnd() {
		return '</p>';
	}

	public function textBlock($text) {
		return $this->p($text);
	}

	public function boldBlock($text) {
		return $this->p($text, "nd_bold");
	}

	public function subtitleBlock($text, $label = null, $level = null) {
		return $this->p($text, "nd_subtitle" . $level, $label);
	}

	public function propertyBlock($name, $text) {
		return $this->textBlock("<b>{$name}</b>: {$text}");
	}
	
	public function text($text, $class = null) {
		if ($class) {
			$class = " class=\"{$class}\"";
		}
		
		return "<span{$class}>{$text}</span>";
	}

	function url($url, $text, $title = null, $style = null, $rel = null, $data = null) {
		if ($title) {
			$title = " title=\"{$title}\"";
		}

		if ($style) {
			$style = " class=\"{$style}\"";
		}

		if ($rel) {
			$rel = " rel=\"{$rel}\"";
		}
		
		if (is_array($data)) {
			$data = implode(array_map(function($k, $v) {
				return " data-{$k}=\"{$v}\"";
			}, array_keys($data), $data));
		}

		return "<a href=\"{$url}\"{$title}{$style}{$rel}{$data}>{$text}</a>";
	}

	public function colorBlock($color, $content) {
		return "<span style=\"color: {$color}\">{$content}</span>";
	}

	public function padLeft($text, $pad) {
		if ($pad > 0) {
			$class = " class=\"pad{$pad}\"";
		}
		
		return "<div{$class}>{$text}</div>";
	}

	private function arrayToClassString($classes) {
		$result = '';
		if (count($classes) > 0) {
			$c = implode(' ', $classes);
			$result = " class=\"{$c}\"";
		}
		
		return $result;
	}

	public function image($tag, $source, $alt = null, $width = 0, $height = 0, $thumb = null) {
		$imgText = null;

		$divClasses = [ 'img' ];
		$imgClasses = [];
		
		$mainTag = 'figure';
		$captionTag = 'figcaption';
		
		switch ($tag) {
			case 'rightimg':
				$divClasses[] = 'img-right';
				break;
				
			case 'leftimg':
				$divClasses[] = 'img-left';
				break;

			case 'img':
				//$divClasses[] = 'img-center';
				//$imgClasses[] = 'center';
				$mainTag = 'div';
				$captionTag = 'div';
				break;
		}

		if ($source) {
			if ($alt) {
				$alt = htmlspecialchars($alt, ENT_QUOTES);
				$imgAttrText .= " title=\"{$alt}\"";
				$subText = "<{$captionTag} class=\"img-caption\">{$alt}</{$captionTag}>";
			}
			
			$imgSrc = $thumb ?? $source;

			$imgClasses[] = 'img-responsive';

			if ($width > 0) {
				$imgAttrText .= " width=\"{$width}\"";
				$thumb = $imgSrc;
			}

			if ($height > 0) {
				$imgAttrText .= " height=\"{$height}\"";
			}
			
			$imgClassText = $this->arrayToClassString($imgClasses);
			$divClassText = $this->arrayToClassString($divClasses);

			$imgText = "<img src=\"{$imgSrc}\"{$imgClassText}{$imgAttrText} />";

			if ($thumb) {
				$imgText = "<a href=\"{$source}\" class=\"colorbox\">{$imgText}</a>";
			}

			$imgText = "<{$mainTag}{$divClassText}>{$imgText}{$subText}</{$mainTag}>";
		}

		return $imgText;
	}

	public function youtubeBlock($code, $width = 0, $height = 0) {
		if ($width > 0) {
			$widthText = " width=\"{$width}\"";
		}
		
		if ($height > 0) {
			$heightText = " height=\"{$height}\"";
		}
		
		if ($width == 0 && $height == 0) {
			$divClass = ' class="embed-responsive embed-responsive-16by9"';
			$iFrameClass = ' class="embed-responsive-item"';
		}
		else {
			$divClass = ' class="center"';
		}
		
		return "<div{$divClass}><iframe{$iFrameClass} src=\"https://www.youtube.com/embed/{$code}\"{$widthText}{$heightText} frameborder=\"0\" allowfullscreen></iframe></div>";
	}

	public function quoteBlock($text, $author, $url = null, $date = null) {
		$header = null;

		if ($date) {
			$date = "[{$date}]";
		}

		if ($author || $date) {
			if ($author) {
				if ($url) {
					$author = $this->url($url, $author);
				}

				$author = "<span class=\"quote-author\">{$author}</span>";
				
				if ($date) {
					$date = ' ' . $date;
				}
			}

			$header = "<div class=\"quote-header\">{$author}{$date}:</div>";
		}

		$result = "<div class=\"quote\">{$header}<div class=\"quote-body\">{$text}</div></div>";

		return $result;
	}

	protected function divBlock($id, $title, $body, $visible = false) {
		$shortid = "short" . $id;
		$fullid = "full" . $id;

		$shortstyle = $visible ? "none" : "block";
		$fullstyle = $visible ? "block" : "none";

		$short = "<div id=\"{$shortid}\" style=\"display:{$shortstyle};\">
				<span class=\"spoiler-header\" onclick=\"{$fullid}.style.display='block'; {$shortid}.style.display='none';\">{$title} <span class=\"glyphicon glyphicon-chevron-right\" aria-hidden=\"true\"></span></span>
				</div>";

		$full = "<div id=\"{$fullid}\" style=\"display:{$fullstyle};\">
				<span class=\"spoiler-header\" onclick=\"{$fullid}.style.display='none';{$shortid}.style.display='block';\">{$title} <span class=\"glyphicon glyphicon-chevron-down\" aria-hidden=\"true\"></span></span>
				<div class=\"spoiler-body\">{$body}</div>
			</div>";

		return $short . $full;
	}

	public function spoilerBlock($content, $label = null) {
		$label = $label ?? 'Спойлер';

		$id = mt_rand();

		$div = $this->divBlock($id, $label, $content);

		return "<div class=\"spoiler\">{$div}</div>";
	}
	
	public function next() {
		return '<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>';
	}
	
	public function prev() {
		return '<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>';
	}
	
	public function list($items, $ordered = false) {
		$tag = $ordered ? 'ol' : 'ul';

		$items = array_map(function($item) {
			return '<li>' . $item . '</li>';
		}, $items);
					
		return  '<' . $tag . '>' . implode($items) . '</' . $tag . '>';
	}
	
	public function readMore($url, $label = 'Читать дальше') {
		return "<div class=\"read-more\"><a href=\"{$url}\">{$label} &raquo;&raquo;</a></div>";
	}
}
