<?php

/**
 * Enqueue Google Fonts.
 */

function ww_genesis_scripts_styles() {
    wp_enqueue_style( 'rextrophies-fonts', ww_genesis_fonts_url(), array(), null );
}
add_action( 'wp_enqueue_scripts', 'ww_genesis_scripts_styles' );

function ww_genesis_fonts_url() {
    $fonts_url = '';

    /* Translators: If there are characters in your language that are not
    * supported by Lora, translate this to 'off'. Do not translate
    * into your own language.
    */
    $worksans = _x( 'on', 'worksans font: on or off', 'rextrophies' );

    /* Translators: If there are characters in your language that are not
    * supported by Open Sans, translate this to 'off'. Do not translate
    * into your own language.
    */
    // $source_sans = _x( 'on', 'Source Sans font: on or off', 'ww_genesis' );

    // if ( 'off' !== $worksans || 'off' !== $source_sans ) {
    if ( 'off' !== $worksans) {
        $font_families = array();

        // if ( 'off' !== if ( 'off' !== $worksans || 'off' !== $source_sans ) { ) {
        if ( 'off' !== $worksans  ) {
            $font_families[] = 'Work Sans:300&text=SINCE1980,400,700,900';
        }

        // if ( 'off' !== $source_sans ) {
        //     $font_families[] = 'Source Sans Pro:400,400italic,600,700';
        // }

        $query_args = array(
            'family' => urlencode( implode( '|', $font_families ) ),
            'subset' => urlencode( 'latin,latin-ext' ),
        );

        $fonts_url = add_query_arg( $query_args, '//fonts.googleapis.com/css' );
    }

    return $fonts_url;
}
