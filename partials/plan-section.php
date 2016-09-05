<header>
	<h2>Plan Your Visit</h2>
</header>

<article class="map-box">

	<div class="intro">

		<p>Lorem ipsum esterio dolor sit amet, consectetur adipicing elit etiam vitae porta aterosia, tristique elitas purus nulla, posuere acsia esi estibulum rutrum eliter luctus.</p>

		<h3>Make Your Selection</h3>

		<?php
		$post_type = 'poi';
		$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type ) );
		foreach( $taxonomies as $taxonomy ) : 
			$terms = get_terms( $taxonomy );
			foreach( $terms as $term ) : ?>
				<h3><?php echo $term->slug; ?></h3>
				<div class="pois">
				<?php $args = array( 'taxonomy' => $taxonomy,'taxonomy' => $term->slug, 'post_type' => 'poi', 'posts_per_page' => -1 );
				$loop = new WP_Query( $args );
				while ( $loop->have_posts() ) : $loop->the_post();
					$poi_location = get_field('poi_map'); ?>
					<p><a class="map_link" href="#" rel="<?php echo $poi_location['lat']; ?>, <?php echo $poi_location['lng']; ?>"><?php the_title(); ?></a></p>
				<?php endwhile; ?>
				</div>
			<?php endforeach;
		endforeach;
		?>

	</div>

	<?php $planning_map = get_field('planning_map', 'options');
	if( !empty($planning_map) ):
	?>
	<div class="map">

		<div class="marker" data-icon="<?php echo get_template_directory_uri(); ?>/assets/map-green.png" data-lat="<?php echo $planning_map['lat']; ?>" data-lng="<?php echo $planning_map['lng']; ?>">
		</div>
		
		<?php $args = array( 'post_type' => 'poi', 'posts_per_page' => -1 );
		$loop = new WP_Query( $args );
		while ( $loop->have_posts() ) : $loop->the_post();
			$poi_location = get_field('poi_map'); ?>
			<div class="marker" data-icon="<?php echo get_template_directory_uri(); ?>/assets/map-red.png" data-lat="<?php echo $poi_location['lat']; ?>" data-lng="<?php echo $poi_location['lng']; ?>">
				<h4><?php echo the_title(); ?></h4>
			</div>
		<?php endwhile; ?>
	</div>
	<?php endif; ?>
	
</article>