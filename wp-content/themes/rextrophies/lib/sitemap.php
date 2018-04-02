<?php

function ww_sitemap() {
$ww_sitemap = '';

  $ww_sitemap .= '<ul>';
  $pages = wp_list_pages(array('echo' => 0, 'title_li' => 0));

$ww_sitemap .=  $pages;

  $ww_sitemap .= '<ul>';
 
 return $ww_sitemap;
}

add_shortcode( 'ww-sitemap','ww_sitemap' );