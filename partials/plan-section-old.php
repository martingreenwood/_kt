<header>
	<h2>Plan Your Visit</h2>
</header>

<article class="map-box">

	<div class="intro">

		<?php the_field('planning_intro', 'options'); ?>

		<h3>Make Your Selection</h3>

		<dl class="accordion">
		<?php
		$post_type = 'poi';
		$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type ) );
		foreach( $taxonomies as $taxonomy ) : 
			$terms = get_terms( $taxonomy );
			foreach( $terms as $term ) : ?>
				<dt><a href=""><?php echo $term->slug; ?></a> <i class="fa fa-caret-square-o-down" aria-hidden="true"></i></dt>
				<dd>
				<?php $args = array( 'taxonomy' => $taxonomy, 'term' => $term->slug, 'post_type' => $post_type, 'posts_per_page' => -1 );
				$loop = new WP_Query( $args );
				while ( $loop->have_posts() ) : $loop->the_post();
					$poi_location = get_field('poi_map'); ?>
					<a class="map_link" href="#" rel="<?php echo $poi_location['lat']; ?>, <?php echo $poi_location['lng']; ?>"><?php the_title(); ?></a>
				<?php 
				endwhile; 
				wp_reset_postdata(); wp_reset_query(); ?>
				</dd>
			<?php endforeach;
		endforeach;
		?>
		</dl>

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
		<?php endwhile; 
		wp_reset_postdata(); wp_reset_query(); ?>
	</div>
	<?php endif; ?>
	
</article>