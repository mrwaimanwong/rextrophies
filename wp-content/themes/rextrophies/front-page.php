<?php

/**
 * Home Page.
 *
 * @category   Genesis Child Theme
 * @package    Templates
 * @subpackage Home
 * @author     Wai Man Wong
 * @link       http://www.waimanwong.com/
 * @since      1.0.0
 */


add_action( 'get_header', 'ww_home_helper' );

/**
 * Add widget support for homepage. If no widgets active, display the default loop.
 *
 */
function ww_home_helper() {

	// if ( is_active_sidebar( 'home-settle' ) || is_active_sidebar( 'home-about' ) || is_active_sidebar( 'home-case-types' ) || is_active_sidebar( 'home-meettheteam' ) || is_active_sidebar( 'home-founders' )) {

			remove_action( 'genesis_loop', 'genesis_do_loop' );
			add_action( 'genesis_loop', 'ww_homepage' );

			/** Force Full Width */
			add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
			add_filter( 'genesis_site_layout', '__genesis_return_full_width_content' );
	// }
}

function ww_homepage() {
echo '<section class="hero-container">

		<div class="wrap">

		<table class="hero">
		<tbody>
		<tr>
			<td>
				<h2>North County’s Trophy Shop</h2>
				<div class="sub-header">Since 1980</div>
				<a href="/contact" class="button">How Can We Help?</a>
			</td>
		</tr>

		<tr>
			<td class="hero-footer">
					<span class="hero-footer-header">Hours:</span> Mon-Fri 10AM — 5PM &nbsp;&nbsp;&nbsp;<i class="icon-map-marker"></i>12817 Poway Road, Poway, CA 92064
			</td>
		</tr>
		</tbody>
	</table>
</div>
</section>

<section class="products-container">
	<div class="wrap">
			<div class="products">
			<h2>Rex Trophies Carries a Large Selection of Awards & Gifts</h2>
			<p>Most awards can be engraved and customized to suit your needs. Call us today for details <a href="tel://858-748-5951">858-748-5951</a>.</p>

				<div class="item one-third first">
					<a href="/product-category/trophies/"><i class="icon-trophies"></i>
					<h3>Trophies</h3></a>
				</div>

				<div class="item one-third">
					<a href="/product-category/acrylics/"><i class="icon-acrylics"></i>
					<h3>Acrylics</h3></a>
				</div>

				<div class="item one-third">
					<a href="/product-category/plaques/"><i class="icon-plaques"></i>
					<h3>Plaques</h3></a>
				</div>

				<div class="item one-third first">
					<a href="/product-category/medals/"><i class="icon-medals"></i>
					<h3>Medals</h3></a>
				</div>

				<div class="item one-third ">
					<a href="/engraving/"><i class="icon-nameplates"></i>
					<h3>Engraving</h3></a>
				</div>

				<div class="item one-third">
					<a href="/product-category/corporate-gifts/"><i class="icon-mantel_clock"></i>
					<h3>Corporate Gifts</h3></a>
				</div>

			</div>
	</div>
</section>

<section class="info-bar-container">
	<div class="wrap">
			<div class="info-bar">
				<span class="hero-footer-header">Hours:</span> Mon-Fri 10AM — 5PM &nbsp;&nbsp;&nbsp;<br /><i class="icon-map-marker"></i>12817 Poway Road, Poway, CA 92064 &nbsp;&nbsp;&nbsp;<br /><div class="phone-number"><i class="icon-phone"></i><a href="tel://858-748-5951">858-748-5951</a></div>
			</div>
		</div>
	</section>

	<section class="spotlight-container">
		<div class="wrap">

			<div class="spotlight-left one-half first">
				<h3>Plaques</h3>
				<p>Plaques are a perfect way to show recognition to that deserving person. We offer a wide variety of wood design shapes and plate options. Please view our selection of most popular plaques or visit us at Rex Trophies for more ideas to suit your specific needs.
				<ul>
				<li><a href="product-category/plaques/perpetual-plaques/">Perpetual Plaques</a></li>
				<li><a href="/product-category/plaques/rosewood-piano-finish-plaques/">Rosewood Piano Finish Plaques</a></li>
				<li><a href="/product-category/plaques/solid-walnut-plaques/">Solid Walnut Plaques</a></li>
				</ul></p>
				<a href="/product-category/plaques/" class="button">Browse Plaques</a>
			</div>

			<div class="spotlight-right one-half">
			<h3>Trophies</h3>
			<p>Whether you need school sports awards, participation trophies, or a fantasy football perpetual cup, Rex Trophies has you covered! We carry a large selection of resins, cups, and a large selection of plastic figures to create your own custom styled trophy.
			<ul>
			<li><a href="/product-category/trophies/trophy-cups/">Trophy Cups</a></li>
			<li><a href="/product-category/trophies/resin-trophies/">Resin Trophies</a></li>
			</ul></p>
			<a href="/build-a-trophy/" class="button">Build a Trophy</a>
			</div>


		</div>

	</section>

	<section class="about-container">
		<div class="wrap">

			<div class="about">
			<h2>Poway Trophy Shop Since 1980</h2>
			<p>Rex Trophies has been helping the Poway Community recognize excellence and celebrate achievement since 1980. We offer <a href="/product-category/trophies/trophy-cups/">Trophy Cups</a>, <a href="/product-category/trophies/resin-trophies/">Resin Trophies</a>, <a href="/product-category/plaques/perpetual-plaques/">Perpetual Plaques</a>, <a href="/product-category/plaques/rosewood-piano-finish-plaques/">Rosewood Piano Finish Plaques</a>, <a href="/product-category/plaques/solid-walnut-plaques/">Solid Walnut Plaques</a>, <a href="product-category/acrylics/">Acrylics</a>, <a href="/product-category/corporate-gifts/">Corporate Gifts</a>, <a href="/product-category/medals/">Medals</a> and <a href="/engraving/">Engraving</a> for sports, corporate, golf tournaments, fantasy football and much more. We also carry a variety of gift items such as picture frames,  desk clock & pen sets, business card holders, desk name plates, and pewter & glass mugs. We serve all the communities of San Diego North County including: Vista Oceanside, San Marcos, Escondido, Poway, Rancho Bernardo, Carlsbad, Solana Beach.</p>

			</div>


		</div>

	</section>

';
}

genesis();
