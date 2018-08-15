<?php

add_action('genesis_setup', 'ww_remove_footer');

function ww_remove_footer() {
	/** Genesis - Remove header and footer markup */
	remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
	remove_action( 'genesis_footer', 'genesis_do_footer' );
	remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );
}


// add_action('genesis_setup', 'ww_add_footer_widgets');
//
// function ww_add_footer_widgets() {
// 	add_theme_support( 'genesis-footer-widgets', 3 ); //Need to add .footer-widgets-4 to the CSS if you want a fourth
// }



/* Customize the entire footer */

add_action( 'genesis_footer', 'ww_custom_footer' );

function ww_custom_footer() {

	$name = get_bloginfo ('name');
	$url = get_bloginfo ('url');
	$footernav = wp_nav_menu( array('theme_location' => 'Footer Navigation Menu', 'menu' => 'Footer Menu', 'container' => 'nav', 'container_class' => 'nav-footer', 'menu_class' => 'menu menu-footer', 'echo' => false, 'before' => '<i class="icon-long-arrow-right"></i>',));

	echo '

	<div class="footer-section-container">
		<div class="wrap">
			<div class="footer-section">

				<div class="footer-info one-fourth first">
				<h1 class="site-title" itemprop="headline"><a href="'.$url .'" title="'.$name.'">'.$name.'</a></h1>
				<p class="footer-address">12817 Poway Road<br />Poway , CA 92064</p>
				<p><span class="hero-footer-header">Phone:</span> <a href="tel://858-748-5951">858-748-5951</a><br /><span class="hero-footer-header">Email:</span> <a href="mailto:info@rextrophies.com">info@rextrophies.com</a></p>
				<ul class="social-links">
					<li><a href="mailto:info@rextrophies.com" target="_blank"><i class="icon-envelope-square"></i></a></li>
					<li><a href="https://www.instagram.com/rextrophies/" target="_blank"><i class="icon-instagram"></i></a></li>
					<li><a href="https://www.facebook.com/Rex-Trophies-517955255215390/" target="_blank"><i class="icon-facebook-square"></i></a></li>
				</ul>

				</div>

				<div class="footer-contact one-half">
					<h3 class="contact-header">Trophy Questions?</h3>
					'.do_shortcode( '[contact-form-7 id="49" title="Contact form 1"]' ).'
				</div>

				<div class="footer-nav one-fourth">
				'.$footernav.'
		 		</div>

			</div>
		</div>
	</div>

	<div class="site-footer">
		<div class="wrap">
			<div class="footer-copyright">©'.date('Y').' Rex Trophies  - All Rights Reserved</div>
			<div class="footer-credit">Website Created By <a href="https://waimanwong.com" target="blank">Wai Man Wong</a></div>
		</div>
	</div>';
}
