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

/**
 *
 * REDIRECT SINGE SEARCH TO FIRST POST.
 *
 */
add_action('template_redirect', 'redirect_single_post');
function redirect_single_post() {
    if (is_search()) {
        global $wp_query;
        if ($wp_query->post_count == 1) {
            wp_redirect( get_permalink( $wp_query->posts['0']->ID ) );
        }
    }
}

function limit_posts_per_search_page() {
	if ( is_search() )
		set_query_var('posts_per_archive_page', 11); 
}
add_filter('pre_get_posts', 'limit_posts_per_search_page');

//sub manu walker
class KT_Sublevel_Walker extends Walker_Nav_Menu
{
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<div class='sub-menu-wrap'><div class='sub-menu'><ul class='sub-items'>\n";
    }
    function end_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul></div></div>\n";
    }
}

