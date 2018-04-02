<?php

add_action('genesis_setup', 'ww_custom_layout', 11);

function ww_custom_layout() {
	/** Unregister site layouts */
	genesis_unregister_layout( 'sidebar-content-sidebar' );
	genesis_unregister_layout( 'sidebar-sidebar-content' );
	genesis_unregister_layout( 'content-sidebar-sidebar' );

	// Remove the header right widget area
	unregister_sidebar( 'header-right' );

	/** Remove secondary sidebar */
	unregister_sidebar( 'sidebar-alt' );

}
