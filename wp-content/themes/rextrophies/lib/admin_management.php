<?php

add_action('genesis_setup', 'ww_edit_admin_features', 11);

function ww_edit_admin_features() {
	
	//* Remove Genesis Layout Settings
	remove_theme_support( 'genesis-inpost-layouts' );

	//Remove Wordpress Admin Bar
	add_filter('show_admin_bar', '__return_false');

}

//* Unregister Genesis widgets
add_action( 'widgets_init', 'unregister_genesis_widgets', 20 );

function unregister_genesis_widgets() {
	unregister_widget( 'Genesis_eNews_Updates' );
	unregister_widget( 'Genesis_Featured_Page' );
	//unregister_widget( 'Genesis_Featured_Post' );
	//unregister_widget( 'Genesis_Latest_Tweets_Widget' );
	unregister_widget( 'Genesis_Menu_Pages_Widget' );
	unregister_widget( 'Genesis_User_Profile_Widget' );
	//unregister_widget( 'Genesis_Widget_Menu_Categories' );
}