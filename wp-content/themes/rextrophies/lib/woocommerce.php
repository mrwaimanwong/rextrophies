<?php

add_action( 'after_setup_theme', 'woocommerce_support');

function woocommerce_support() {
		add_theme_support( 'woocommerce' );
}

add_action( 'after_setup_theme', 'woocommerce_support_images');

function woocommerce_support_images() {
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}

if ( class_exists( 'WooCommerce' ) ) {
  add_filter( 'wp_nav_menu_items', 'rex_custom_menu_item', 10, 2 );

}

// add_filter( 'wp_nav_menu_items', 'rex_custom_menu_item', 10, 2 );

function rex_custom_menu_item ( $items, $args ) {

    if ($args->theme_location == 'Primary') {

      if( WC()->cart->get_cart_contents_count() === 1 ){
        $items .= '<li class="menu-item rex-menu-item-cart"><a href="'. WC()->cart->get_cart_url() .'"title="View your shopping cart"><i class="fa fa-shopping-cart"></i> '. WC()->cart->cart_contents_count .' Item - '. WC()->cart->get_cart_total() .'</a></li>';
      }

      else if( WC()->cart->get_cart_contents_count() > 1 ){
        $items .= '<li class="menu-item rex-menu-item-cart"><a href="'. WC()->cart->get_cart_url() .'"title="View your shopping cart"><i class="fa fa-shopping-cart"></i> '. WC()->cart->cart_contents_count .' Items - '. WC()->cart->get_cart_total() .'</a></li>';
      }

    }
    return $items;
}

add_filter( 'woocommerce_add_to_cart_fragments', 'rex_cart_count_fragments', 10, 1 );

function rex_cart_count_fragments( $fragments ) {

  if( WC()->cart->get_cart_contents_count() === 1 ){
  $fragments['li.rex-menu-item-cart'] = '<li class="menu-item rex-menu-item-cart"><a href="'. WC()->cart->get_cart_url() .'"title="View your shopping cart"><i class="fa fa-shopping-cart"></i> '. WC()->cart->cart_contents_count .' Item - '. WC()->cart->get_cart_total() .'</a></li>';
  }

  else if( WC()->cart->get_cart_contents_count() > 1 ){
    $fragments['li.rex-menu-item-cart'] = '<li class="menu-item rex-menu-item-cart"><a href="'. WC()->cart->get_cart_url() .'"title="View your shopping cart"><i class="fa fa-shopping-cart"></i> '. WC()->cart->cart_contents_count .' Items - '. WC()->cart->get_cart_total() .'</a></li>';
  }

  else {
    $fragments['li.rex-menu-item-cart'] = '';
  }

    return $fragments;

}

add_filter( 'loop_shop_per_page', 'new_loop_shop_per_page', 20 );

function new_loop_shop_per_page( $cols ) {
  // $cols contains the current number of products per page based on the value stored on Options -> Reading
  // Return the number of products you wanna show per page.
  $cols = 12;
  return $cols;
}

/**********************************
*
* Integrate WooCommerce with Genesis.
*
* Unhook WooCommerce wrappers and
* Replace with Genesis wrappers.
*
* Reference Genesis file:
* genesis/lib/framework.php
*
* @author AlphaBlossom / Tony Eppright
* @link http://www.alphablossom.com
*
**********************************/

// Add WooCommerce support for Genesis layouts (sidebar, full-width, etc) - Thank you Kelly Murray/David Wang
add_post_type_support( 'product', array( 'genesis-layouts', 'genesis-seo' ) );

// Unhook WooCommerce Sidebar - use Genesis Sidebars instead
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

// Unhook WooCommerce wrappers
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

// Hook new functions with Genesis wrappers
add_action( 'woocommerce_before_main_content', 'rex_my_theme_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'rex_my_theme_wrapper_end', 10 );

// Add opening wrapper before WooCommerce loop
function rex_my_theme_wrapper_start() {

    do_action( 'genesis_before_content_sidebar_wrap' );
    genesis_markup( array(
        'html5' => '<div %s>',
        'xhtml' => '<div id="content-sidebar-wrap">',
        'context' => 'content-sidebar-wrap',
    ) );

    do_action( 'genesis_before_content' );
    genesis_markup( array(
        'html5' => '<main %s>',
        'xhtml' => '<div id="content" class="hfeed">',
        'context' => 'content',
    ) );
    do_action( 'genesis_before_loop' );

}

/* Add closing wrapper after WooCommerce loop */
function rex_my_theme_wrapper_end() {

    do_action( 'genesis_after_loop' );
    genesis_markup( array(
        'html5' => '</main>', //* end .content
        'xhtml' => '</div>', //* end #content
    ) );
    do_action( 'genesis_after_content' );

    echo '</div>'; //* end .content-sidebar-wrap or #content-sidebar-wrap
    do_action( 'genesis_after_content_sidebar_wrap' );

}
