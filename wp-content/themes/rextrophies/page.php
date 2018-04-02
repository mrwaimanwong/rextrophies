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
	remove_action('genesis_entry_header', 'genesis_do_post_title');
		add_action( 'genesis_after_header', 'ww_page' );
		// add_action( 'genesis_entry_footer', 'ww_add_page_nav' );
}

function ww_page() {
	$page_title = get_the_title();
echo '
<div class="page-header-container">

	<div class="wrap">
	<div class="page-header">
			<header class="entry-header">
				<h1 class="entry-title" itemprop="headline">'.$page_title.'</h1>
			</header>
		</div>
	</div>
</div>
';
}

function ww_add_page_nav() {

	$prevlink = previous_post_link('<div class="page-nav left">&laquo;%link</div>');
	$nextlink = next_post_link('<div class="page-nav right">%link&raquo;</div>');
	return $prevlink.$nextlink;
}

genesis();
