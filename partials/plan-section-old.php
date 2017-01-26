<header>
	<h2>Plan Your Visit</h2>
</header>

<article class="map-box">

	<?php 
	
	// https://maps.googleapis.com/maps/api/place/radarsearch/json?location=54.326890,-2.747581&radius=5000&type=museum&key=AIzaSyDcE-HKUQ_hzqDNWic-jom-6Usv68xtd9I

	// 54.326890,-2.747581
	
	$local_art_gallery = get_remote_data('https://maps.googleapis.com/maps/api/place/radarsearch/json?location=54.326890,-2.747581&radius=3000&type=art_gallery&key=AIzaSyDcE-HKUQ_hzqDNWic-jom-6Usv68xtd9I');
	$local_art_gallery_obj = json_decode($local_art_gallery);

	$local_lodging = get_remote_data('https://maps.googleapis.com/maps/api/place/radarsearch/json?location=54.326890,-2.747581&radius=3000&type=lodging&key=AIzaSyDcE-HKUQ_hzqDNWic-jom-6Usv68xtd9I');
	$local_lodging_obj = json_decode($local_lodging);

	?>

	<div class="intro">

		<?php the_field('planning_intro', 'options'); ?>

		<h3>Make Your Selection</h3>

		<ul>
			<li>
				<span>Art</span> <input id="artCheckbox" name="art_toggle" value="art" type="checkbox">
			</li>
			<li>
				<span>Lodging / Acommodation</span> <input id="logdingCheckbox" name="lodging_toggle" value="lodging" type="checkbox">
			</li>
		</ul>

		<?php 
		/*
		
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

		*/ 
		?>

	</div>

	<?php 
	$planning_map = get_field('planning_map', 'options');
	if( !empty($planning_map) ):
	?>
	<div class="map">

		<div class="marker" data-icon="<?php echo get_template_directory_uri(); ?>/assets/map-pin.png" data-lat="<?php echo $planning_map['lat']; ?>" data-lng="<?php echo $planning_map['lng']; ?>">
		</div>
		
		<?php foreach ($local_art_gallery_obj->results as $local_art_gallery): ?>
		
			<div class="marker art" data-type="art" data-icon="<?php echo get_template_directory_uri(); ?>/assets/map-art.png" data-lat="<?php echo $local_art_gallery->geometry->location->lat; ?>" data-lng="<?php echo $local_art_gallery->geometry->location->lng ?>">
			</div>

		<?php endforeach; ?>

		<?php foreach ($local_lodging_obj->results as $local_lodging): ?>
		
			<div class="marker lodging" data-type="lodging" data-icon="<?php echo get_template_directory_uri(); ?>/assets/map-bed.png" data-lat="<?php echo $local_lodging->geometry->location->lat; ?>" data-lng="<?php echo $local_lodging->geometry->location->lng ?>">
			</div>

		<?php endforeach; ?>


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