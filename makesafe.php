<?php
/*
Plugin Name: Make Safe
Plugin URI: http://www.louddog.com
Description: Dynamically obfuscate mailto links.
Author: Matt DeClaire
Version: 1.0
*/

if (!class_exists("LoudDog_MakeSafe")) {
	class LoudDog_MakeSafe {
		function __construct() {
			if (is_admin()) return;
			add_action('init', array($this, 'start'), 10);
		}
		
		function start() {
			ob_start(array($this, 'filter'));
		}
	
		function filter($content) {
			preg_match_all('/<a.*href="(mailto:.*?)".*?>(.*?)<\/a>/', $content, $matches);
			
			for ($ndx = 0; $ndx < count($matches[0]); $ndx++) {
				$email = '';
				foreach(str_split($matches[1][$ndx]) as $chr) {
					$email .= rand(0,1) ? $chr : "&#".ord($chr).";";
				}

				$text = '';
				foreach(str_split($matches[2][$ndx]) as $chr) {
					$text .= rand(0,1) ? $chr : "&#".ord($chr).";";
				}

				$code = str_split("<a href='$email'>$text</a>", 7);
				$code = "document.write(\"".implode('"+"', $code)."\");";
				$code = str_split($code);
				$code = array_map('ord', $code);
				$code = array_map('dechex', $code);
				$code = "\x".implode("\x", $code);
				$code = "<script type='text/javascript'>"."eval(unescape('$code'));"."</script>";
		
				$content = str_replace($matches[0][$ndx], $code, $content);
			}
	
			return $content;
		}
	}

	new LoudDog_MakeSafe();
}