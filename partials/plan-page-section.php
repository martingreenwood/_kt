<article class="map-box">

	<?php 
	$planning_map = get_field('planning_map', 'options');
	if( !empty($planning_map) ):
	?>
	<div class="map">

		<div class="marker" data-icon="<?php echo get_template_directory_uri(); ?>/assets/map-pin.png" data-lat="<?php echo $planning_map['lat']; ?>" data-lng="<?php echo $planning_map['lng']; ?>">
		</div>
	
		<?php

		/*
		$args = array( 'post_type' => 'poi', 'posts_per_page' => -1 );
		$loop = new WP_Query( $args );
		while ( $loop->have_posts() ) : $loop->the_post();
			$poi_location = get_field('poi_map'); ?>
			<div class="marker" data-icon="<?php echo get_template_directory_uri(); ?>/assets/map-red.png" data-lat="<?php echo $poi_location['lat']; ?>" data-lng="<?php echo $poi_location['lng']; ?>">
				<h4><?php echo the_title(); ?></h4>
			</div>
		<?php endwhile; 
		wp_reset_postdata(); wp_reset_query(); 
		*/ 
		?>

	</div>
	<?php endif; ?>
	
</article>