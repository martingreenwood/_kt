<?php
/**
 * The template for displaying the front page
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package _kt
 */

get_header(); ?>

	<section class="slider">

		<div class="slides">
			<?php $home_slides = get_field('slider'); if( $home_slides ): ?>
			<?php foreach( $home_slides as $home_slide ): ?>
			<div class="slide">
				<?php if(get_field('internal_url', $home_slide['id'])): ?>
				<a href="<?php echo get_field('internal_url', $home_slide['id']) ?>">
				<?php endif; ?>
					<?php echo wp_get_attachment_image( $home_slide['id'], '169cover' ); ?>
					<div class="caption">
						<h2><?php echo $home_slide['title']; ?></h2>
						<h3><?php echo $home_slide['caption']; ?></h3>
					</div>
				<?php if(get_field('internal_url', $home_slide['id'])): ?>
				</a>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<div class="arrow-box">
			<div class="container">
				<div class="arrows">
				</div>
			</div>
		</div>
		
	</section>

	<section id="discover">
		<div class="container">

		<?php get_template_part( 'partials/discover', 'section' ); ?>

		</div>
	</section>

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>

	<section id="explore">
		<div class="container">

		<?php get_template_part( 'partials/explore', 'section' ); ?>

		</div>
	</section>

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>

	<section id="experience">
		<div class="container">

		<?php get_template_part( 'partials/experience', 'section' ); ?>

		</div>
	</section>

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>

	<section id="plan">
		<div class="container">

		<?php get_template_part( 'partials/plan', 'section' ); ?>

		</div>
	</section>

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>

	<section id="signup">
		<div class="container">

		<?php get_template_part( 'partials/signup', 'section' ); ?>

		</div>
	</section>

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>

	<section id="blog">
		<div class="container">

		<?php get_template_part( 'partials/blog', 'section' ); ?>

		</div>
	</section>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
			//while ( have_posts() ) : the_post();

				//get_template_part( 'template-parts/content', 'page' );

			//endwhile; // End of the loop.
			?>

		</main>
	</div>

<?php
get_footer();
