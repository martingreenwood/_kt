<?php
/**
 * Template Name: Info Page
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
						<div class="table"><div class="cell middle">
						<h2><?php echo $home_slide['title']; ?></h2>
						<h3><?php echo $home_slide['caption']; ?></h3>
						</div></div>
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
				get_template_part( 'template-parts/content', 'page-alt' );
			endwhile; 
			?>
			</div>
		</div>

		<main id="main" class="site-main row" role="main">
				
			<div class="column">

				<?php 
				if( have_rows('cr_repeater') ): while ( have_rows('cr_repeater') ) : the_row(); 
					?>
					<article class="row">

					<?php 
					$image = get_sub_field('cr_image'); $size = 'full';
					if( $image ) {
					echo wp_get_attachment_image( $image, $size );
					}
					?>

					<div class="caption">
						<?php the_sub_field('cr_caption'); ?> 
					</div>

					<div class="copy">
						<?php the_sub_field('cr_content'); ?>
					</div>

					</article>
					<?php
				endwhile; endif; 
				?>

			</div>

			<aside>

				<div class="open">
					<h3>Essential Info</h3>
					<?php the_field('opening_times'); ?>
					<p><?php the_field('website'); ?></p>
				</div>

				<div class="location">
					<!--<h3>GET DIRECTIONS</h3>-->
					<?php  $location = get_field('location');
					if( !empty($location) ):
					?>
					<div class="map">
						<div class="marker" data-lat="<?php echo $location['lat']; ?>" data-lng="<?php echo $location['lng']; ?>"></div>
					</div>
					<?php endif; ?>
				</div>

				<!--<div class="website">
					<h3>VISIT WEBSITE</h3>
				</div>-->
				
			</aside>

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

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>

	<section id="whatson">
		<div class="container">

		<?php get_template_part( 'partials/try', 'section' ); ?>

		</div>
	</section>

<?php
get_footer();
