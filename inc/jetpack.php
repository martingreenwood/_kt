<?php
/**
 * Jetpack Compatibility File.
 *
 * @link https://jetpack.com/
 *
 * @package _kt
 */

/**
 * Jetpack setup function.
 *
 * See: https://jetpack.com/support/infinite-scroll/
 * See: https://jetpack.com/support/responsive-videos/
 */
function _kt_jetpack_setup() {
	// Add theme support for Infinite Scroll.

	add_theme_support( 'infinite-scroll', array(
		'container' 	 => 'searchcontiner',
		'footer' 		 => false,
	    'type'           => 'scroll',
	    'footer_widgets' => false,
	    'container'      => 'content',
	    'wrapper'        => true,
	    'render'         => false,
	    'posts_per_page' => '11',

	) );

	// Add theme support for Responsive Videos.
	add_theme_support( 'jetpack-responsive-videos' );
}
add_action( 'after_setup_theme', '_kt_jetpack_setup' );

/**
 * Custom render function for Infinite Scroll.
 */
function _kt_infinite_scroll_render() {
	while ( have_posts() ) {
		the_post();
		if ( is_search() ) :
			get_template_part( 'template-parts/content', 'search' );
		else :
			get_template_part( 'template-parts/content', get_post_format() );
		endif;
	}
}