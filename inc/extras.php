<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package _kt
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function _kt_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	return $classes;
}
add_filter( 'body_class', '_kt_body_classes' );

/**
 *
 * OPTIONS
 *
 */

if( function_exists('acf_add_options_page') ) {
	acf_add_options_page('Theme Options');
}

function _kt_acf_init() {
	acf_update_setting('google_api_key', 'AIzaSyDC6NXY8XZrS6mELrD8_Dj3Hg_OTqHret8');
}
add_action('acf/init', '_kt_acf_init');