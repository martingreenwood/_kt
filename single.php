<?php
/**
 * The template for displaying all single posts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package _kt
 */

get_header(); ?>

	<section id="breadcrumbs">
		<div class="container">
			<div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
			<?php 
			if(function_exists('bcn_display'))
			{
				bcn_display();
			}
			?>
			</div>
		</div>
	</section>

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>

	<section class="title">
		<div class="container">
			<h1><?php the_title(); ?></h1>
		</div>
	</section>

	<div id="primary" class="content-area container">

		<main id="main" class="site-main row" role="main">
				
			<div class="column">

				<?php
				while ( have_posts() ) : the_post();

					get_template_part( 'template-parts/content', get_post_format() );

					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;

				endwhile; // End of the loop.
				?>

			</div>

			<?php
				// gens aside 
				get_sidebar();
			?>

		</main>
	</div>

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>

	<section id="share">
		<div class="container">

		<?php get_template_part( 'partials/share', 'section' ); ?>

		</div>
	</section>

<?php
get_footer();
