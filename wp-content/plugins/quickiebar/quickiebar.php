<?php
/*
Plugin Name: QuickieBar
Plugin URI: https://quickiebar.com
Description: QuickieBar makes it easy for you to convert visitors by adding an attractive and easily customizable conversion bar to the top or bottom of your site.
Version: 1.8.4
Author: Phil Baylog
Author URI: https://quickiebar.com
License: GPLv2
*/

//Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

define( 'QB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'QB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

global $QB_VERSION;
$QB_VERSION = '1.8.4';

class QuickieBar{

	private static $instance;

	private function __construct(){

		$this->include_before_plugin_loaded();
		add_action('plugins_loaded', array($this, 'include_after_plugin_loaded'));

		add_action('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link_to_plugins_page'));
	}

	/** Singleton Instance Implementation **********/
	public static function instance(){
		if ( !isset( self::$instance ) ){
			self::$instance = new QuickieBar();
			self::$instance->init();
			//self::$instance->load_textdomain();
		}
		return self::$instance;
	}

	//called before the 'plugins_loaded action is fired
	function include_before_plugin_loaded(){
		global $wpdb;

		$wpdb->qb_bars = $wpdb->prefix . 'qb_bars';
		$wpdb->qb_views = $wpdb->prefix . 'qb_views';
		$wpdb->qb_conversions = $wpdb->prefix . 'qb_conversions';
	}

	function add_settings_link_to_plugins_page($links){

		$settings_url = admin_url('admin.php?page=quickiebar-settings');
		$settings_link = '<a href="' . $settings_url . '">Settings</a>';

	  array_unshift($links, $settings_link);

	  return $links;
	}

	function admin_menu(){
		add_menu_page( 'QuickieBar', 'QuickieBar', 'manage_options', 'quickiebar', 'quickiebar', QB_PLUGIN_URL . 'admin/images/menu-icon.png', '41.4' );
	}

	/*Utility function for determining whether WP is doing ajax call*/
	function is_ajax_call(){
		return defined('DOING_AJAX') && DOING_AJAX;
	}

	//called after the 'plugins_loaded action is fired
	function include_after_plugin_loaded(){

		global $QB_VERSION;

		//If user is activating the plugin for the first time
		if(!get_option('QB_VERSION') && !quickiebar()->is_ajax_call() && is_admin()){

			$settings_url = admin_url('admin.php?page=quickiebar');

			$html = '';

			$html .= '<div class="updated" style="border-color:#EF4651;padding:5px;">';
			 $html .= '<p style="margin-left:10px;">Thanks for installing QuickieBar! &nbsp;&nbsp;&nbsp;<a class="coral-bg" href="' . $settings_url . '" style="position:relative;background:#EF4651;color:#FFF;padding:3px 6px;cursor:pointer;border-radius:3px;letter-spacing:.05em;font-size:12px;font-weight:bold;">GET STARTED WITH QUICKIEBAR &nbsp;<i class="fa fa-long-arrow-right"></i></a></p>';
			$html .= '</div><!--/.updated-->';

			echo $html;
		}

		//update database or options if plugin version updated
		if(get_option('qb_version') != $QB_VERSION){
			quickiebar()->initializeQBOptions();
			quickiebar()->initializeQBDB();

			update_option('qb_version', $QB_VERSION);
		}

		//admin only includes
		if( is_admin() || $this->is_ajax_call() ){

			//add action for admin_menu
			//we want to do this after including/adding actions for submenus to prevent duplicated "bars" page
			if( current_user_can('manage_options') ){
				add_action('admin_menu', array($this, 'admin_menu'));
			}

			//Order matters. We have to include whichever submenu will replace the "main" menu first.
			//Load either only the setup page (if user has not finished setting up conversion reports)
			//...Or load the standard Bars / Conversions / Settings pages
			if(!get_option('qb_setup_complete')){
				include_once( QB_PLUGIN_PATH . 'admin/controllers/setup.php');
			}
			else{
				include_once( QB_PLUGIN_PATH . 'admin/controllers/bars.php');
				include_once( QB_PLUGIN_PATH . 'admin/controllers/conversions.php');
				include_once( QB_PLUGIN_PATH . 'admin/controllers/settings.php');
				include_once( QB_PLUGIN_PATH . 'admin/controllers/help.php');
			}
		}

		if($this->is_ajax_call()){
			//include ajax handler for processing ajax calls made from admin pages
			include_once( QB_PLUGIN_PATH . 'admin/ajax/qb-ajax-handler.php');
		}

		add_action( 'wp_print_scripts',					array( $this, 'print_scripts'		) );
		add_action( 'admin_print_scripts',			array( $this, 'print_scripts'	) );
		add_action( 'wp_print_styles',					array( $this, 'print_styles'			) );
		add_action( 'admin_print_styles',				array( $this, 'print_styles'	) );

	}

	private function init(){


	}

	static function initializeQBOptions(){
		//setup initial qb options if not currently set
		if(!get_option('qb_attribution')){
			update_option('qb_attribution', 'hidden');
		}
		if(!get_option('qb_visibility')){
			update_option('qb_visibility', 'everywhere');
		}
		if(!get_option('qb_setup_complete')){
			update_option('qb_setup_complete', false);
		}
		if(!get_option('qb_email')){
			update_option('qb_email', '');
		}
		if(!get_option('qb_subscribed')){
			update_option('qb_subscribed', false);
		}
		if(!get_option('qb_fixed_compatibility')){
			update_option('qb_fixed_compatibility', 'off');
		}
		if(!get_option('qb_debug_mode')){
			update_option('qb_debug_mode', 'off');
		}

		//New options with 1.1.0
		if(!get_option('qb_page_visibility')){
			update_option('qb_page_visibility', 'show');
		}
		if(!get_option('qb_post_visibility')){
			update_option('qb_post_visibility', 'show');
		}
		if(!get_option('qb_page_exceptions')){
			update_option('qb_page_exceptions', 'false');
		}
		if(!get_option('qb_post_exceptions')){
			update_option('qb_post_exceptions', 'false');
		}

		//New options with 1.2.0
		if(!get_option('qb_device_visibility')){
			update_option('qb_device_visibility', 'all');
		}

		//New options with 1.5.0
		if(!get_option('qb_category_visibility')){
			update_option('qb_category_visibility', 'show');
		}
		if(!get_option('qb_category_exceptions')){
			update_option('qb_category_exceptions', 'false');
		}

		//New options with 1.6.0
		if(!get_option('qb_archive_page_visibility')){
			update_option('qb_archive_page_visibility', 'hide');
		}
		if(!get_option('qb_bar_zindex')){
			update_option('qb_bar_zindex', '100');
		}

		//New options with 1.8.0
		if(!get_option('qb_custom_post_type_visibility')){
			update_option('qb_custom_post_type_visibility', 'hide');
		}

	}

	static function initializeQBDB(){
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		//NOT SUPPORTED YET
		//$multisite_prefix = ( is_multisite() ? $wpdb->prefix : '' );

		$sql = " CREATE TABLE " . $wpdb->qb_bars . " (
			id int(9) NOT NULL AUTO_INCREMENT,
			created_date datetime NOT NULL,
			status varchar(55) DEFAULT 'paused' NOT NULL,
			color_bar_background char(7) DEFAULT '' NOT NULL,
			color_button_background char(7) DEFAULT '' NOT NULL,
			color_bar_text char(7) DEFAULT '' NOT NULL,
			color_button_text char(7) DEFAULT '' NOT NULL,
			bar_height varchar(55) DEFAULT 'regular' NOT NULL,
			bar_text varchar(4095) DEFAULT '' NOT NULL,
			button_text varchar(1019) DEFAULT '' NOT NULL,
			destination varchar(1019) DEFAULT '' NOT NULL,
			new_tab varchar(55) DEFAULT 'enabled' NOT NULL,
			placement varchar(55) DEFAULT 'top' NOT NULL,
			devices varchar(55) DEFAULT 'all' NOT NULL,
			alignment varchar(55) DEFAULT 'leftright' NOT NULL,
			sticky varchar(55) DEFAULT 'enabled' NOT NULL,
			animation varchar(55) DEFAULT 'slidein' NOT NULL,
			button_style varchar(55) DEFAULT 'rounded' NOT NULL,
			close_button_visibility varchar(55) DEFAULT 'onhover' NOT NULL,
			bar_uuid varchar(13) NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY bar_uuid_index (bar_uuid)
			) " . $charset_collate . " AUTO_INCREMENT=1;";

		$sql .= " CREATE TABLE " . $wpdb->qb_views . " (
			id int(9) NOT NULL AUTO_INCREMENT,
			created_date datetime NOT NULL,
			user_uuid varchar(13) NOT NULL,
			bar_uuid varchar(13) NOT NULL,
			PRIMARY KEY  (id),
			KEY user_uuid_index (user_uuid),
			KEY bar_uuid_index (bar_uuid)
			) " . $charset_collate . " AUTO_INCREMENT=1;";

		$sql .= " CREATE TABLE " . $wpdb->qb_conversions . " (
			id int(9) NOT NULL AUTO_INCREMENT,
			created_date datetime NOT NULL,
			user_uuid varchar(13) NOT NULL,
			bar_uuid varchar(13) NOT NULL,
			PRIMARY KEY  (id),
			KEY user_uuid_index (user_uuid),
			KEY bar_uuid_index (bar_uuid)
			) " . $charset_collate . " AUTO_INCREMENT=1;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			dbDelta($sql);

			//1.0.0 initial version
			//1.7.1 updated lengths of varchar column types for bar_text, button_text, and destination
			//to increase support for html + foreign characters
			//1.8.2 added names for primary, unique, and key columns to fix bug w/ duplicated entries being created on dbDelta db updating
			//1.8.3 fixed sql error w/ prior version
			add_option('qb_db_version', '1.8.2');
	}

	static function destroyQBOptions(){
		delete_option('qb_version');
		delete_option('qb_attribution');
		delete_option('qb_visibility');
		delete_option('qb_page_visibility');
		delete_option('qb_page_exceptions');
		delete_option('qb_post_visibility');
		delete_option('qb_post_exceptions');
		delete_option('qb_category_visibility');
		delete_option('qb_category_exceptions');
		delete_option('qb_setup_complete');
		delete_option('qb_email');
		delete_option('qb_subscribed');
		delete_option('qb_fixed_compatibility');
		delete_option('qb_debug_mode');
		delete_option('qb_device_visibility');
		delete_option('qb_archive_page_visibility');
		delete_option('qb_bar_zindex');
	}

	static function destroyQBDB(){
		global $wpdb;

		$sql = "DROP TABLE IF EXISTS " . $wpdb->qb_bars . ", " . $wpdb->qb_views . ", " . $wpdb->qb_conversions . ";";

		$wpdb->query($sql);
	}

	static function activate(){


	}

	static function deactivate(){
		//This should be done every time plugin is deactivated
	}

	/*Delete all quickiebar options, bars, and conversion data, and deactivate the plugin*/
	static function deactivateAndDestroyQBData(){

		global $QB_VERSION;

		quickiebar()->destroyQBOptions();
		quickiebar()->destroyQBDB();

		//if plugin is in default folder name
		if(is_plugin_active('quickiebar/quickiebar.php')){
			deactivate_plugins('quickiebar/quickiebar.php');
		}
		//if plugin is in versioned folder name
		if(is_plugin_active('quickiebar-' . $QB_VERSION . '/quickiebar.php')){
			deactivate_plugins('quickiebar-' . $QB_VERSION . '/quickiebar.php');
		}
		//if plugin is in beta folder name
		if(is_plugin_active('quickiebar-beta/quickiebar.php')){
			deactivate_plugins('quickiebar-beta/quickiebar.php');
		}
	}

	static function load_quickiebar_script(){
		global $QB_VERSION;

		wp_enqueue_script('quickiebar', QB_PLUGIN_URL . 'public/js/qb.js', array( 'jquery' ), $QB_VERSION, false);
		wp_localize_script('quickiebar', 'ajaxurl', admin_url('admin-ajax.php') );

		$qb_public_globals = array( 'QB_PUBLIC_NONCE' => wp_create_nonce('qb_public_nonce') );

		//set global variable that tells QB to display even if hidden by admin
		if(current_user_can('manage_options')){
			$qb_public_globals['USER_TYPE'] = "admin";
		}
		else{
			$qb_public_globals['USER_TYPE'] = "public";
		}

		wp_localize_script('quickiebar', 'QB_PUBLIC_GLOBALS', $qb_public_globals );
	}

	function print_scripts(){

		global $QB_VERSION;

		if( is_admin() && current_user_can('manage_options') ){

			wp_enqueue_script('knockout', QB_PLUGIN_URL . 'admin/js/inc/knockout-3.2.0.js', array('jquery'), '3.2.0', true);
			wp_enqueue_script('knockout-mapping', QB_PLUGIN_URL . 'admin/js/inc/knockout-mapping-2.4.1.js', array('jquery', 'knockout'), '2.4.1', true);

		}

		//if we're loading an admin page, or options dictate that we should load quickiebar
		if($this->should_load_quickiebar_script()){

			//print quickiebar script whenever appropriate according to options
			$this->load_quickiebar_script();

			/*TODO determine if it's in end users best interest to include all QB information at this stage - i.e. in a synchronous way*/
			/*$bars = qb_bars::get_bars();
			wp_localize_script('quickiebar', 'qb_bar', $bars );*/
		}

	}

	static function should_load_quickiebar_script(){

		//DON'T LOAD if admin is viewing admin page (unless it's bars page of course - which is handled by bars.php)
		if( is_admin()){
			return false;
		}

		//Always LOAD the bar if Debug Mode is turned on (FrontEnd javascript will prevent the bar from actually showing unless #qbshow is specified in the URL)
		if(get_option('qb_debug_mode') == 'on'){
			return true;
		}

		/*If user agent is IE with Version < 10*/
		if(preg_match('/(?i)msie [4-9]/',$_SERVER['HTTP_USER_AGENT'])){
			return false;
		}

		//some pages we never show quickiebar on...such as the login & register pages
		if(in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))){
			return false;
		}

		$visibility = get_option('qb_visibility');

		if($visibility == 'everywhere'){
			return true;
		}
		else if($visibility == 'pagesonly' && (is_page() || is_home())){//note that is_home() is required because a static blog page won't return true for is_page, but it will for is_home
			return true;
		}
		else if($visibility == 'postsonly' && is_singular('post')){
			return true;
		}
		else if($visibility == 'homepageonly' && is_front_page()){
			return true;
		}
		else if($visibility == 'custom'){
			//Visibility is Custom
			//We need to check attributions of the page against rules the user has set up
			if(is_page() || is_home()){//note that is_home() is required because a static blog page won't return true for is_page, but it will for is_home

				$page_id = get_the_ID();
				$page_visibility = get_option('qb_page_visibility');
				$page_exceptions = json_decode(get_option('qb_page_exceptions'));

				//if is_home() page, we need to look up the REAL page id
				if(is_home()){
					$page_id = get_option('page_for_posts');
				}

				//if we decoded that there are no exceptions, convert this to an empty array so we can "search" it anyway below
				if($page_exceptions == false){
					$page_exceptions = array();
				}

				//if page visibility is set to SHOW and page IS NOT on the exceptions list
				if($page_visibility == 'show' && !in_array($page_id, $page_exceptions)){
					return true;
				}
				//if page visibility is set to HIDE and page IS on the exceptions list
				else if($page_visibility == 'hide' && in_array($page_id, $page_exceptions)){
					return true;
				}
				else{
					return false;
				}

			}
			else if(is_singular('post')){

				$post_id = get_the_ID();
				$post_categories = get_the_category($post_id);
				$post_visibility = get_option('qb_post_visibility');
				$post_exceptions = json_decode(get_option('qb_post_exceptions'));
				$category_visibility = get_option('qb_category_visibility');
				$category_exceptions = json_decode(get_option('qb_category_exceptions'));

				//if we decoded that there are no exceptions, convert this to an empty array so we can "search" it anyway below
				if($post_exceptions == false){
					$post_exceptions = array();
				}
				if($category_exceptions == false){
					$category_exceptions = array();
				}

				$post_has_category_on_exception_list = false;

				if($post_categories && count($post_categories) > 0){

					//loop through all category exceptions, and for each exception look up in array of posts' categories
					foreach($category_exceptions as $category_exception){
						foreach($post_categories as $post_category){
							if($category_exception == $post_category->cat_ID){
								$post_has_category_on_exception_list = true;
							}
						}
					}
				}

				//determine visibility by post settings
				if($post_visibility == 'hide' || count($post_exceptions) > 0){

					//if page visibility is set to SHOW and page IS NOT on the exceptions list
					if($post_visibility == 'show' && !in_array($post_id, $post_exceptions)){
						return true;
					}
					//if page visibility is set to HIDE and page IS on the exceptions list
					else if($post_visibility == 'hide' && in_array($post_id, $post_exceptions)){
						return true;
					}
					else{
						return false;
					}

				}
				else if($category_visibility == 'hide' || count($category_exceptions) > 0){
					//if page visibility is set to SHOW and page IS NOT on the exceptions list
					if($category_visibility == 'show' && !$post_has_category_on_exception_list){
						return true;
					}
					//if page visibility is set to HIDE and page IS on the exceptions list
					else if($category_visibility == 'hide' && $post_has_category_on_exception_list){
						return true;
					}
					else{
						return false;
					}
				}
				else{
					//no post or category visibility settings set
					return true;
				}

			}
			else if(is_archive()){
				$archive_page_visibility = get_option('qb_archive_page_visibility');

				if($archive_page_visibility == 'show'){
					return true;
				}
				else{
					return false;
				}

			}
			else if(is_singular() && !is_singular('post') && !is_page() && !is_attachment()){
				//custom post type

				$custom_post_type_visibility = get_option('qb_custom_post_type_visibility');

				if($custom_post_type_visibility == 'show'){
					return true;
				}
				else{
					return false;
				}
			}
			else{
				//no idea what kind of page/post/archive/category this is...
			}
		}

		//settings don't dictate that we should load quickiebar on this page
		return false;
	}

	function print_styles(){

		global $QB_VERSION;

		//if admin...
		if( is_admin() ){
			wp_enqueue_style('quickiebar-admin', QB_PLUGIN_URL . 'admin/style/quickiebar.css', false, microtime(), 'all');
		}

		if(is_admin() && get_admin_page_title() == 'Bars'){
			wp_enqueue_style('colpick', QB_PLUGIN_URL . 'admin/js/inc/colpick/css/colpick.css', false, '2.0.2', 'all');
			wp_enqueue_style('tooltipster', QB_PLUGIN_URL . 'admin/js/inc/tooltipster/tooltipster.css', false, '0.0.0', 'all');
		}

		//always...
		wp_enqueue_style( 'fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', false, '4.3.0', 'all' );
		wp_enqueue_style( 'google-font-montserrat', '//fonts.googleapis.com/css?family=Montserrat:400', false );
		wp_enqueue_style( 'google-font-roboto-slab', '//fonts.googleapis.com/css?family=Roboto+Slab:400', false );

		//quickiebar style
		wp_enqueue_style( 'qb', QB_PLUGIN_URL . 'public/style/qb.css', false, $QB_VERSION, 'all');

	}
}/*end QuickieBar class*/

//The main function used to retrieve the one true QuickieBar instance (a shortcut for QuickieBar::instance())
function quickiebar(){
	return QuickieBar::instance();
}

//initialize
quickiebar();

//activation
if(is_admin()){
	register_activation_hook( __FILE__, array( 'QuickieBar', 'activate') );
}

//deactivation
if(is_admin()){
	register_deactivation_hook( __FILE__, array( 'QuickieBar', 'deactivate') );
}
?>
