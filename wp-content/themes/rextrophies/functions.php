<?php
/*
Child Theme Name: Rex Trophies
Author: Wai Man Wong
Version: 1.0
URL: htp://waimanwong.com/
*/

/** Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit( 'Cheatin&#8217; uh?' );

require_once( get_stylesheet_directory() . '/lib/init.php');
require_once( get_stylesheet_directory() . '/lib/scripts.php');
require_once( get_stylesheet_directory() . '/lib/content_management.php');
require_once( get_stylesheet_directory() . '/lib/siteicons.php');
require_once( get_stylesheet_directory() . '/lib/fonts.php');
require_once( get_stylesheet_directory() . '/lib/custom_header.php');
require_once( get_stylesheet_directory() . '/lib/nav_locations.php');
require_once( get_stylesheet_directory() . '/lib/layout.php');
require_once( get_stylesheet_directory() . '/lib/custom_footer.php');
require_once( get_stylesheet_directory() . '/lib/woocommerce.php');
require_once( get_stylesheet_directory() . '/lib/custom_api.php');
// require_once( get_stylesheet_directory() . '/lib/sitemap.php');
// require_once( get_stylesheet_directory() . '/lib/admin_management.php');

// Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'Rex Trophies' );
define( 'CHILD_THEME_URL', 'http://www.waimanwong.com/' );
define( 'CHILD_DOMAIN', 'rextrophies' );
