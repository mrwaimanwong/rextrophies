<?php

// we're going to fire this off when we activate the child theme
add_action('genesis_setup','ww_theme_setup', 15);

// THEME SETUP TIME **************************************
function ww_theme_setup () {

	// don't update theme (it's custom right? so you don't need updates)
	add_filter( 'http_request_args', 'ww_dont_update', 5, 2 );

	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list' ) );

	/** Add Viewport meta tag for mobile browsers */
	add_action( 'genesis_meta', 'ww_child_viewport_meta_tag' );

	/** Remove Edit Link */
	add_filter( 'edit_post_link', '__return_false' );

	//Allow shortcodes in widgets
	add_filter('widget_text', 'do_shortcode');

	/*If you're going to use the date picker in Contact Form 7*/
	//add_filter( 'wpcf7_support_html5_fallback', '__return_true' );


add_action( 'after_setup_theme', 'woocommerce_support' );
add_theme_support( 'wc-product-gallery-zoom' );
add_theme_support( 'wc-product-gallery-lightbox' );
add_theme_support( 'wc-product-gallery-slider' );
add_filter( 'loop_shop_per_page', 'new_loop_shop_per_page', 20 );
}

function woocommerce_support() {
		add_theme_support( 'woocommerce' );
}

function new_loop_shop_per_page( $cols ) {
// $cols contains the current number of products per page based on the value stored on Options -> Reading
// Return the number of products you wanna show per page.
$cols = 12;
return $cols;
}

/** Add Viewport meta tag for mobile browsers */
function ww_child_viewport_meta_tag() {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
}

/*
if you name your child theme something that already exists in the
wordpress repo, then you may get an alert offering a "theme update"
for a theme that's not even yours. Weird, I know. Anyway, here's a
fix for that.

credit: Mark Jaquith
http://markjaquith.wordpress.com/2009/12/14/excluding-your-plugin-or-theme-from-update-checks/
*/
function ww_dont_update( $r, $url ) {
	if ( 0 !== strpos( $url, 'http://api.wordpress.org/themes/update-check' ) )
		return $r; // Not a theme update request. Bail immediately.
	$themes = unserialize( $r['body']['themes'] );
	unset( $themes[ get_option( 'template' ) ] );
	unset( $themes[ get_option( 'stylesheet' ) ] );
	$r['body']['themes'] = serialize( $themes );
	return $r;
}

/** Auto update themes and plugins **/
add_filter( 'auto_update_plugin', '__return_true' );
add_filter( 'auto_update_theme', '__return_true' );

// // Create a custom post type
// add_action( 'init', 'ww_custom_post_type' );
//
// function ww_custom_post_type() {
//
//    $labels = array(
//     'name' => __( 'Homepage Slider' ),
//     'singular_name' => __( 'Homepage Slider' ),
//     'all_items' => __('All Homepage Slides'),
//     'add_new' => _x('Add new Homepage Slide', 'Home Sections'),
//     'add_new_item' => __('Add new Homepage Slide'),
//     'edit_item' => __('Edit Homepage Slide'),
//     'new_item' => __('New Homepage Slide'),
//     'view_item' => __('View Homepage Slide'),
//     'search_items' => __('Search in Homepage Slides'),
//     'not_found' =>  __('No Homepage Slides found'),
//     'not_found_in_trash' => __('No Homepage Slides found in trash'),
//     'parent_item_colon' => ''
//     );
//
//     $args = array(
//     'labels' => $labels,
//     'public' => true,
//     'has_archive' => true,
//     'menu_position' => 2,
//     'menu_icon' => 'dashicons-admin-generic',
// 	'rewrite' => array('slug' => 'homepage_slider'),
// 	'taxonomies' => array( 'category', 'post_tag' ),
// 	'supports'  => array( 'title', 'editor', 'revisions', 'thumbnail', 'genesis-cpt-archives-settings' )
// 	// 'supports'  => array( 'title', 'editor', 'revisions', 'excerpt', 'custom-fields', 'comments',)
// 	);
//
//   register_post_type( 'homepage_slider', $args);
// }
