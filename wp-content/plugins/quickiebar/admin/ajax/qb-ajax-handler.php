<?php

/*
* qb_ajax_handler
*
* @description: handler for all admin ajax calls
*
*/

class qb_ajax_handler{
	
	function __construct(){
		add_action('wp_ajax_qb_admin_ajax', array($this, 'process_admin_ajax') );
		add_action('wp_ajax_nopriv_qb_public_ajax', array($this, 'process_ajax') );
		
		//Calls made by logged in users to public endpoints
		add_action('wp_ajax_qb_public_ajax', array($this, 'process_ajax') );
	}
	
	function process_ajax(){
		
		//security check using wp nonce param http://codex.wordpress.org/WordPress_Nonces
		if( !check_ajax_referer('qb_public_nonce', 'qb_public_nonce', false) ){
			die('security test failed');
		}
		
		switch($_REQUEST['endpoint']){
			case 'get_bar':
				echo qb_bars::get_bar('json');
			break;
			case 'save_view':
				echo qb_conversions::save_view($_REQUEST['user_uuid'], $_REQUEST['bar_uuid'], false);
			break;
			case 'save_conversion':
				echo qb_conversions::save_conversion($_REQUEST['user_uuid'], $_REQUEST['bar_uuid'], false);
			break;
		}
		
		die();
	}
	
	function process_admin_ajax(){
		
		//security check using wp nonce param http://codex.wordpress.org/WordPress_Nonces
		if( !check_ajax_referer('qb_admin_nonce', 'qb_admin_nonce', false) || !current_user_can('manage_options') ){
			die('security test failed');
		}
		
		switch($_REQUEST['endpoint']){
			case 'get_bars':
				echo qb_bars::get_bars('json');
			break;
			case 'create_bar':
				echo qb_bars::create_bar($_REQUEST['options'], 'json');
			break;
			case 'update_bar':
				echo qb_bars::update_bar($_REQUEST['options'], 'json');
			break;
			case 'resume_bar':
				echo qb_bars::resume_bar($_REQUEST['bar_uuid'], 'json');
			break;
			case 'pause_bar':
				echo qb_bars::pause_bar($_REQUEST['bar_uuid'], 'json');
			break;
			case 'delete_bar':
				echo qb_bars::delete_bar($_REQUEST['bar_uuid'], 'json');
			break;
			case 'get_conversions_for_bar':
				echo qb_conversions::get_conversions_for_bar($_REQUEST['bar_uuid'], 'json');
			break;
			case 'update_quickiebar_settings':
				echo qb_settings::save_settings($_REQUEST['settings'], 'json');
			break;
			case 'get_pages_and_posts_and_categories':
				echo qb_settings::get_pages_and_posts_and_categories('json');
			break;
			case 'destroy_plugin_data':
				echo qb_settings::destroy_plugin_data();
			break;
			case 'complete_setup':
				echo qb_setup::complete_setup($_REQUEST['email'], $_REQUEST['subscribed'], 'json');
			break;
			
		}

		die();
	}
}

new qb_ajax_handler();

?>