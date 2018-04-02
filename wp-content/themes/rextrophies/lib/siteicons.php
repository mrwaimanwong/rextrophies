<?php

add_action('wp_head', 'ww_add_siteicons');

function ww_add_siteicons() {
	echo
	'<link rel="icon" href="'.get_stylesheet_directory_uri() .'/images/siteicon_32x32.png?fit=32%2C32" sizes="32x32" />
	<link rel="icon" href="'.get_stylesheet_directory_uri() .'/images/siteicon_192x192.png?fit=192%2C192" sizes="192x192" />
	<link rel="apple-touch-icon-precomposed" href="'.get_stylesheet_directory_uri() .'/images/siteicon_180x180.png?fit=180%2C180">
	<meta name="msapplication-TileImage" content="'.get_stylesheet_directory_uri() .'/images/siteicon_270x270.png?fit=270%2C270">';
}
