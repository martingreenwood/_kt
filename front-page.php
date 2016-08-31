<?php
/**
 * The template for displaying the front page
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package _kt
 */

get_header('lp'); ?>

	<section class="gallery">

		<div class="slide">
			<?php $images = get_field('slider'); if( $images ): ?>
			<?php foreach( $images as $image ): ?>
			<div class="image" style="background-image: url(<?php echo $image['url']; ?>);">
				<div class="overlay"></div>
			</div>
			<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
	</section>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
			while ( have_posts() ) : the_post();

				get_template_part( 'template-parts/content', 'page' );

			endwhile; // End of the loop.
			?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
