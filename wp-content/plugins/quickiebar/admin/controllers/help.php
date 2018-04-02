<?php

/*
* qb_help
*
* @description: conroller for quickiebar help sub menu page
*
*/

class qb_help{

	var $action;

	function __construct(){
		add_action('admin_menu', array($this, 'admin_menu'));
	}

	function admin_menu(){
		$page = add_submenu_page('quickiebar', 'Help', 'Help', 'manage_options', 'quickiebar-help', array($this, 'html') );
	}

	//echo out the settings view (html file) file when loading the bars admin page
	function html(){
		echo file_get_contents(QB_PLUGIN_PATH . 'admin/views/help.html');

		//enqueue scripts for this view
		$this->enqueue_scripts_for_view();

	}

	function enqueue_scripts_for_view(){

	}
}

new qb_help();

?>
