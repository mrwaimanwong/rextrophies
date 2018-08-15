<?php

/** Genesis - Remove header markup */
add_action('genesis_setup', 'ww_remove_header');

function ww_remove_header() {
	remove_action( 'genesis_header', 'genesis_header_markup_open', 5);
	remove_action( 'genesis_header', 'genesis_do_header' );
	remove_action( 'genesis_header', 'genesis_header_markup_close', 15);
  //* Remove default Genesis menu
  remove_action('genesis_after_header', 'genesis_do_nav');
}

/** Add custom header */
add_action('genesis_header', 'ww_custom_header');

function ww_custom_header()
{
	$name = get_bloginfo ('name');
	$url = get_bloginfo ('url');
	$description = get_bloginfo ('description');
	// $primarynav = wp_nav_menu( array('theme_location' => 'primary', 'menu' => 'Menu 1', 'container' => 'nav', 'container_class' => 'nav-primary', 'menu_class' => 'menu genesis-nav-menu menu-primary', 'menu_id' => 'nav', 'echo' => false));
  $primarynav = wp_nav_menu( array('theme_location' => 'Primary', 'menu' => 'Menu 1', 'container' => 'nav', 'container_class' => 'nav-primary', 'menu_class' => 'menu genesis-nav-menu menu-primary', 'menu_id' => 'nav', 'echo' => false));
  $compactnav = wp_nav_menu( array('theme_location' => 'Responsive', 'menu' => 'Footer Menu', 'container' => 'nav', 'container_class' => 'nav-compact', 'menu_class' => 'menu menu-compact', 'menu_id' => 'nav-compact', 'echo' => false));
	// $compactnav = wp_nav_menu();

	/*======================================================  ===================================================
	Sroll nav. Use the code below to have the nav display when scrolled. Edit JS as well. Still needs to be styled for responsive.

	//======================================================  ===================================================*/

	// echo
	// '<header class="site-header-compact" role="banner" itemscope="itemscope" itemtype="http://schema.org/WPHeader">
	// 	<div class="wrap">
	// 		<div class="title-area">
	// 			<h1 class="site-title-compact" itemprop="headline"><a href="'.$url .'" title="'.$name.'">'.$name.'</a></h1>
	// 		</div>
	// 		<nav class="nav-compact" role="navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement">
	//  			<div class="wrap">
	//  				'.$compactnav.'
	//  			</div>
 // 			</nav>
	// 	</div>
	// </header>';
	/*======================================================  ===================================================
	End scroll nav

	//======================================================  ===================================================*/

	echo '<header class="site-header" role="banner" itemscope="itemscope" itemtype="http://schema.org/WPHeader">
		<div class="wrap">
			<div class="title-area">
				<h1 class="site-title" itemprop="headline"><a href="'.$url .'" title="'.$name.'">'.$name.'</a></h1>
			</div>
      <div class="phone-number"><ul><li><a href="tel://858-748-5951">858-748-5951</a></li><li><a href="mailto:info@rextrophies.com" target="_blank"><i class="icon-envelope-square"></i></a><a href="https://www.instagram.com/rextrophies/" target="_blank"><i class="icon-instagram"></i></a><a href="https://www.facebook.com/Rex-Trophies-517955255215390/" target="_blank"><i class="icon-facebook-square"></i></a></li></ul></div>
	 		'.$compactnav.$primarynav.'
		</div>
	</header>';
}
