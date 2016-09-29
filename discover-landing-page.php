<?php
/**
 * Template Name: Discover Landing
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

			<div class="row top">

				<div class="column eq-height one">
				
					<?php
					while ( have_posts() ) : the_post();

						get_template_part( 'template-parts/content', 'page-alt' );

					endwhile;

					$featOne = get_field('feature_one');
					$featOneID = get_post_thumbnail_id( $featOne->ID );
					$featOneURL = wp_get_attachment_image_src( $featOneID, 'full' );
					?>
					<article class="one" style="background-image: url(<?php echo $featOneURL[0]; ?>);">
						<a href="<?php echo get_permalink( $featOne->ID ); ?>">
							<div class="table">
								<div class="cell bottom">
									<span><?php echo get_the_title( $featOne->ID ); ?></span>
								</div>
							</div>
						</a>
					</article>

				</div>

				<div class="column eq-height two">
					<?php
					$featTwo = get_field('feature_two');
					$featTwoID = get_post_thumbnail_id( $featTwo->ID );
					$featTwoURL = wp_get_attachment_image_src( $featTwoID, 'full' );
					?>
					<article class="two" style="background-image: url(<?php echo $featTwoURL[0]; ?>);">
						<a href="<?php echo get_permalink( $featTwo->ID ); ?>">
							<div class="table">
								<div class="cell bottom">
									<span><?php echo get_the_title( $featTwo->ID ); ?></span>
								</div>
							</div>
						</a>
					</article>
				</div>

			</div>


			<div class="row bottom">

				<div class="column one">
					<?php
					$featThree = get_field('feature_three');
					$featThreeID = get_post_thumbnail_id( $featThree->ID );
					$featThreeURL = wp_get_attachment_image_src( $featThreeID, 'full' );
					?>
					<article class="three" style="background-image: url(<?php echo $featThreeURL[0]; ?>);">
						<a href="<?php echo get_permalink( $featThree->ID ); ?>">
							<div class="table">
								<div class="cell bottom">
									<span><?php echo get_the_title( $featThree->ID ); ?></span>
								</div>
							</div>
						</a>
					</article>
				</div>
				
				<div class="column two">
					
					<div class="left eq-height">
						<?php
						$featFour = get_field('feature_four');
						$featFourID = get_post_thumbnail_id( $featFour->ID );
						$featFourURL = wp_get_attachment_image_src( $featFourID, 'full' );
						?>
						<article class="four" style="background-image: url(<?php echo $featFourURL[0]; ?>);">
							<a href="<?php echo get_permalink( $featFour->ID ); ?>">
								<div class="table">
									<div class="cell bottom">
										<span><?php echo get_the_title( $featFour->ID ); ?></span>
									</div>
								</div>
							</a>
						</article>
					</div>
					
					<div class="right eq-height">
						<?php
						$featFive = get_field('feature_five');
						$featFiveID = get_post_thumbnail_id( $featFive->ID );
						$featFiveURL = wp_get_attachment_image_src( $featFiveID, 'full' );
						?>
						<article class="five" style="background-image: url(<?php echo $featFiveURL[0]; ?>);">
							<a href="<?php echo get_permalink( $featFive->ID ); ?>">
								<div class="table">
									<div class="cell bottom">
										<span><?php echo get_the_title( $featFive->ID ); ?></span>
									</div>
								</div>
							</a>
						</article>

						<article class="six">
							<?php echo get_field('feature_text'); ?>
						</article>
					</div>

				</div>				

			</div>

		</main>
	</div>

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

	<section id="whatson">
		<div class="container">

		<?php get_template_part( 'partials/whatson', 'section' ); ?>

		</div>
	</section>

<?php
get_footer();
