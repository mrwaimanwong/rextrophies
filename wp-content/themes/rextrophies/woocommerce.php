<?php

/**
 * Default Page.
 *
 * @category   Genesis Child Theme
 * @package    Templates
 * @subpackage Home
 * @author     Wai Man Wong
 * @link       http://www.waimanwong.com/
 * @since      1.0.0
 */


add_action( 'get_header', 'ww_page_helper' );

function ww_page_helper() {
	// remove_action('genesis_entry_header', 'genesis_do_post_title');
		// add_action( 'genesis_after_header', 'ww_page' );
		remove_action( 'genesis_loop', 'genesis_do_loop' );
		add_action( 'genesis_loop', 'getWooContent' );
		/** Force Full Width */
		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
		add_filter( 'woocommerce_is_purchasable', '__return_false');	
}

// function ww_page() {


// 	global $post;
// $terms = get_the_terms( $post->ID, 'product_cat' );
// foreach ($terms as $term) {
//     $product_cat_id = $term->term_id;
// 		$page_title += $product_cat_id;
//     break;
// }
	// $page_title = get_the_title();
	// $title = get_the_category($page_title);
// 	$title = single_term_title("", false);
// echo '
// <div class="page-header-container">
//
// 	<div class="wrap">
// 	<div class="page-header">
// 			<header class="entry-header">
// 				<h1 class="entry-title" itemprop="headline">'.$title.'</h1>
// 			</header>
// 		</div>
// 	</div>
// </div>
// ';
// }

function getWooContent() {
	woocommerce_content();
}

genesis();
