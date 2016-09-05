<?php
/**
 * The header for our theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package _kt
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">


	<header id="masthead" class="site-header" role="banner">
		<div class="container">

			<div class="table">
				<div class="site-branding cell middle">
				<?php
				if ( function_exists( 'the_custom_logo' ) ) {
					the_custom_logo();
				}
				?>
				</div>

				<div class="site-nav cell bottom">
					<nav id="site-navigation" class="main-navigation" role="navigation">
						<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e( 'Primary Menu', '_kt' ); ?></button>
						<?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_id' => 'primary-menu' ) ); ?>
					</nav>
				</div>
			</div>
		</div>
	</header>

	<div id="content" class="site-content">

		<div id="searchbox">
			<div class="container">
				<a class="close" href="#"><span></span><span></span></a>
				<form method="get" class="search-form" action="<?php echo get_bloginfo('url'); ?>">
					<div>
						<input class="text_input" type="text" value="SEARCH" name="s" id="s" onfocus="if (this.value == 'SEARCH') {this.value = '';}" onblur="if (this.value == '') {this.value = 'SEARCH';}" />
						<input type="submit" class="my-wp-search" id="searchsubmit" value="search" />
					</div>
				</form>
				<div class="popular">
					<h3>Popular Searches</h3>
					<?php echo do_shortcode('[bsearch_heatmap]'); ?>
				</div>
			</div>
		</div>
