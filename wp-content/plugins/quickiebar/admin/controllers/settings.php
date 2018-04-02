<?php

/*
* qb_settings
*
* @description: conroller for quickiebar settings sub menu page
*
*/

class qb_settings{

	var $action;

	function __construct(){
		add_action('admin_menu', array($this, 'admin_menu'));
	}

	function admin_menu(){
		$page = add_submenu_page('quickiebar', 'Settings', 'Settings', 'manage_options', 'quickiebar-settings', array($this, 'html') );
	}

	function subscribe_to_email_list($email_address){
		update_option('qb_subscribed', true);

		/*Subscribe via Mailchimp*/

		return true;
	}

	/*Save the user's quickiebar settings from the settings page*/
	static function save_settings($settings, $format = 'php'){

		update_option('qb_attribution', $settings['attribution']);
		update_option('qb_visibility', $settings['visibility']);
		update_option('qb_email', $settings['email']);
		update_option('qb_subscribed', $settings['subscribed']);
		update_option('qb_fixed_compatibility', $settings['fixed_compatibility']);
		update_option('qb_debug_mode', $settings['debug_mode']);
		update_option('qb_device_visibility', $settings['device_visibility']);
		update_option('qb_bar_zindex', $settings['bar_zindex']);

		//if visibiilty is custom, update custom visibility settings
		if($settings['visibility'] == 'custom'){
			update_option('qb_page_visibility', $settings['page_visibility']);
			update_option('qb_page_exceptions', $settings['page_exceptions']);

			update_option('qb_post_visibility', $settings['post_visibility']);
			update_option('qb_post_exceptions', $settings['post_exceptions']);

			update_option('qb_category_visibility', $settings['category_visibility']);
			update_option('qb_category_exceptions', $settings['category_exceptions']);

			update_option('qb_archive_page_visibility', $settings['archive_page_visibility']);

			update_option('qb_custom_post_type_visibility', $settings['custom_post_type_visibility']);
		}

		$result = true;

		if($format == 'json'){
			return json_encode($result);
		}
		else{
			return $result;
		}

	}

	static function get_pages_and_posts_and_categories($format = 'php'){

		$pages = get_posts(array(
			'post_type' => 'page',
			'posts_per_page' => -1,
			'post_status' => 'publish,private,draft'
		));

		$posts = get_posts(array(
			'posts_per_page' => -1
		));

		$categories = get_categories(array(
		));

		$pages_and_posts_and_categories = array(
			'pages' => $pages,
			'posts' => $posts,
			'categories' => $categories
		);

		if($format == 'json'){
			return json_encode($pages_and_posts_and_categories);
		}
		else{
			return $pages_and_posts_and_categories;
		}

	}

	static function destroy_plugin_data(){
		quickiebar()->deactivateAndDestroyQBData();

		return;
	}

	//echo out the settings view (html file) file when loading the bars admin page
	function html(){
		echo file_get_contents(QB_PLUGIN_PATH . 'admin/views/settings.html');

		//enqueue scripts for this view
		$this->enqueue_scripts_for_view();

	}

	function enqueue_scripts_for_view(){

		wp_enqueue_script('qb-settings', QB_PLUGIN_URL . 'admin/js/settings.js', array('jquery', 'knockout', 'underscore'), microtime(), true);
		wp_localize_script('qb-settings', 'QB_GLOBALS', array( 'QB_ADMIN_NONCE' => wp_create_nonce('qb_admin_nonce') ) );

		wp_localize_script('qb-settings', 'qb_settings', array(
			'attribution' => get_option('qb_attribution'),
			'visibility' => get_option('qb_visibility'),
			'page_visibility' => get_option('qb_page_visibility'),
			'page_exceptions' => get_option('qb_page_exceptions'),
			'post_visibility' => get_option('qb_post_visibility'),
			'post_exceptions' => get_option('qb_post_exceptions'),
			'category_visibility' => get_option('qb_category_visibility'),
			'category_exceptions' => get_option('qb_category_exceptions'),
			'email' => get_option('qb_email'),
			'subscribed' => get_option('qb_subscribed'),
			'fixed_compatibility' => get_option('qb_fixed_compatibility'),
			'debug_mode' => get_option('qb_debug_mode'),
			'fname' => wp_get_current_user()->user_firstname,
			'website' => get_site_url(),
			'device_visibility' => get_option('qb_device_visibility'),
			'archive_page_visibility' => get_option('qb_archive_page_visibility'),
			'bar_zindex' => get_option('qb_bar_zindex'),
			'custom_post_type_visibility' => get_option('qb_custom_post_type_visibility')
		) );
	}
}

new qb_settings();

?>
