<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package _kt
 */

?>

	</div>

</div>

<footer id="colophon">
	<div class="container">
		<div class="logo column">
			<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/logo-white.png" width="190" style="display:block;">
		</div>
		<div class="menu column">
			<?php wp_nav_menu( array( 'theme_location' => 'footer1', 'menu_id' => 'footer-one-menu' ) ); ?>
		</div>
		<div class="menu column">
			<?php wp_nav_menu( array( 'theme_location' => 'footer2', 'menu_id' => 'footer-two-menu' ) ); ?>
		</div>
		<div class="menu column">
			<?php wp_nav_menu( array( 'theme_location' => 'footer3', 'menu_id' => 'footer-three-menu' ) ); ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>

</body>
</html>
