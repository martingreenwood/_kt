<div class="slider">

	<div class="es-slides">
		<?php $explore_slides = get_field('explore_slider'); if( $explore_slides ): ?>
		<?php foreach( $explore_slides as $explore_slide ): ?>
		<div class="slide">
			<?php if(get_field('internal_url', $explore_slide['id'])): ?>
			<a href="<?php echo get_field('internal_url', $explore_slide['id']) ?>">
			<?php endif; ?>
				<?php echo wp_get_attachment_image( $explore_slide['id'], 'large-slider' ); ?>
				<div class="caption">
					<?php echo $explore_slide['title']; ?>
				</div>
			<?php if(get_field('internal_url', $explore_slide['id'])): ?>
			</a>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
		<?php endif; ?>
	</div>

</div>

<div class="content">

	<header>
		<h2>Explore</h2>
	</header>

	<article class="intro">
		<?php the_field('explore_blurb'); ?>
	</article>

</div>