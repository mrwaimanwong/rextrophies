<?php

/*
* qb_setup
*
* @description: conroller for quickiebar initial setup view which shows right after user creates account
*
*/

class qb_setup{

	var $action;

	function __construct(){
		//If setup is not complete, show the setup page in addition to the other pages
		add_action('admin_menu', array($this, 'admin_menu'));
	}

	function admin_menu(){
		$page = add_submenu_page('quickiebar', 'Setup', 'Setup', 'manage_options', 'quickiebar', array($this, 'html') );
	}

	static function complete_setup($email_address, $subscribed = false, $format = 'php'){

		update_option('qb_setup_complete', true);
		update_option('qb_email', $email_address);
		update_option('qb_subscribed', ($subscribed == "true" ? true : false));

		$result = true;

		if($format == 'json'){
			return json_encode($result);
		}
		else{
			return $result;
		}
	}

	//echo out the settings view (html file) file when loading the bars admin page
	function html(){
		echo file_get_contents(QB_PLUGIN_PATH . 'admin/views/setup.html');

		//enqueue scripts for this view
		$this->enqueue_scripts_for_view();
	}

	function enqueue_scripts_for_view(){

		//get the current user's email address for populating the setup form
		$current_user = wp_get_current_user();
		$user_email = $current_user->user_email;

		wp_enqueue_script('qb-setup', QB_PLUGIN_URL . 'admin/js/setup.js', array('jquery', 'knockout', 'underscore'), microtime(), true);
		wp_localize_script('qb-setup', 'QB_GLOBALS', array( 'QB_ADMIN_NONCE' => wp_create_nonce('qb_admin_nonce') ) );

		wp_localize_script('qb-setup', 'qb_setup', array(
			'email' => wp_get_current_user()->user_email,
			'fname' => wp_get_current_user()->user_firstname,
			'website' => get_site_url()
		) );

	}
}

new qb_setup();

?>
