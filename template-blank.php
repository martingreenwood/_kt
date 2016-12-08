<?php
/**
 * Template Name: Blank
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

		<div class="row">
			<div class="column">
			<?php 
			while ( have_posts() ) : the_post();
				get_template_part( 'template-parts/content', 'page' );
			endwhile; 
			?>
			</div>
		</div>

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
