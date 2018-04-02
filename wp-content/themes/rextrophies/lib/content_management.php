<?php
//remove junk from head
add_action('init', 'ww_head_cleanup');

function ww_head_cleanup() {
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'feed_links', 2);
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'feed_links_extra', 3);
	remove_action('wp_head', 'start_post_rel_link', 10, 0);
	remove_action('wp_head', 'parent_post_rel_link', 10, 0);
	remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
}

add_action( 'genesis_entry_footer', 'genesis_prev_next_post_nav' );

//Remove <p> tag from images
//add_action('init', 'ww_code_cleanup');

// function ww_code_cleanup() {
// 	add_filter('the_content', 'filter_ptags_on_images');
//
// 	remove_filter ('the_content', 'wpautop');
// }

// Custom loop to hide categories from all archives.


/** CANNOT GET THE FOLLOWING TO WORK**/
// 	add_action('genesis_setup', 'ww_manage_content');
//
//
// function ww_manage_content(){
// 	/** Replace the standard loop with our custom loop */
// 	remove_action( 'genesis_loop', 'genesis_do_loop' );
// 	add_action( 'genesis_loop', 'ww_remove_categories' );
// }
//
// function ww_remove_categories(){
// 	$excluded_categories = 6; //'testimonials'
// 	global $paged; // current paginated page
//   global $query_args; // grab the current wp_query() args
//   $args = array(
//       'category__not_in' => $excluded_categories, // exclude posts from this category
//       'paged'            => $paged, // respect pagination
//   );
//   genesis_custom_loop( wp_parse_args($query_args, $args) );
// }
