<?php

// activate/add additional scripts
add_action('wp_enqueue_scripts','add_child_scripts');

function add_child_scripts()
{

	wp_register_style('ww_main_css', get_stylesheet_directory_uri() . '/css/main.min.css', false, '9880649384aea9f1ee166331c0a30daa');
	wp_enqueue_style('ww_main_css');

	wp_register_script( 'ww_main_js', get_stylesheet_directory_uri() . '/js/scripts.min.js', array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script('ww_main_js');
}
