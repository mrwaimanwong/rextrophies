<?php

add_action('genesis_setup', 'ww_setup_nav');

function ww_setup_nav() {

	// remove_action('genesis_after_header', 'genesis_do_nav');

	// Remove Custom Menu support - blindly removes all menu support (do not use with add_theme_support)
	// remove_theme_support ( 'genesis-menus' );

	// Add Menu support - removes menu support for anything other than Primary and Footer (i.e. Secondary)
	add_theme_support(
		'genesis-menus',
		array(
			'primary' => __( 'Primary Navigation Menu', CHILD_DOMAIN ),
			'footer' => __( 'Footer Navigation Menu', CHILD_DOMAIN ),
		)
	);
}

// add_action('genesis_header', 'ww_primary_nav');

// function ww_primary_nav() {
//
// 	$primarynav = wp_nav_menu( array('theme_location' => 'primary', 'container' => false, 'menu_id' => 'nav', 'echo' => false));
//
// 	echo '<nav class="nav-primary" role="navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement">
// 			<div class="wrap">
// 				'.$primarynav.'
// 			</div>
// 		</nav>';
// }
