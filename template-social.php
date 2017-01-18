<?php
/**
 * Template Name: Social Wall
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

	<div id="primary" class="content-area container">

		<main id="main" class="site-main row" role="main">

			<aside class="logo">
			<?php 
			$social_logo = get_field('social_logo');
			$size = 'full'; // (thumbnail, medium, large, full or custom size)
			if( $social_logo ) {
				echo wp_get_attachment_image( $social_logo, $size );
			}
			?>
			</aside>
				
			<div class="column">
			<?php 
			while ( have_posts() ) : the_post();
				get_template_part( 'template-parts/content', 'page-title' );
			endwhile; 
			?>
			</div>

		</main>
	</div>

	<section id="social">
		<div class="container">

			<?php juicer_feed('name=visitkendal'); ?>
			
		</div>
	</section>	

	<section id="share">
		<div class="container">

		<?php get_template_part( 'partials/share', 'section' ); ?>

		</div>
	</section>

<?php
get_footer();
