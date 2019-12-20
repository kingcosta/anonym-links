<?php
/**
Plugin Name: Anonym Links
Plugin URI: https://github.com/kingcosta/anonym-links
Description: Anonymous, hide referer, nofollow, target=_blank all external links with Automattic's anonym.to service with SSL support.
Author: Dominik Costanzo
Version: 1.0
*/

defined('ABSPATH')||die;

function anon_buffer_start() {
	ob_start("anon_links"); 
}

function anon_buffer_end() {
	ob_end_flush();
}

add_action('wp_head', 'anon_buffer_start');
add_action('wp_footer', 'anon_buffer_end');

function anon_links( $content ) {
	
	$re = "/<a\\s[^>]*href=(\"|'*.?)([^\"|' >]*?)\\1[^>]*>/siu"; 
	if(preg_match_all($re, $content, $matches, PREG_SET_ORDER)) {
		if( !empty($matches) ) {
			
			for ($i=0; $i < count($matches); $i++)
			{
				$noFollow = '';
				$anon_perfix = 'https://anonym.to/?';
				
				//You can add more to this list
				$anon_exception = array($_SERVER['HTTP_HOST'], 'http://example.com/test');		
				
				$tag = $matches[$i][0];
				$tag2 = $matches[$i][0];
				$url = $matches[$i][2];
				
				$pattern = '/target\s*=\s*"\s*_blank\s*"/';
				preg_match($pattern, $url, $match, PREG_OFFSET_CAPTURE);
				if( count($match) < 1 )
					$noFollow .= ' target="_blank" ';
					
				$pattern = '/rel\s*=\s*"\s*[n|d]ofollow\s*"/';
				preg_match($pattern, $url, $match, PREG_OFFSET_CAPTURE);
				if( count($match) < 1 )
					$noFollow .= ' rel="nofollow" ';

				if(stripos($url,'http') !== false & !anon_find($url, $anon_exception)){
					$tag = rtrim ($tag,'>');
					$tag .= $noFollow.'>';
					$tag = str_replace($url, $anon_perfix.$url, $tag);
					$content = str_replace($tag2,$tag,$content);
				}
			}
		}
	}
	return $content;
}

function anon_find($needle, array $haystack) {
    foreach ($haystack as $key => $value)
        if (false !== stripos($needle, $value)) 
            return true;
    return false;
}