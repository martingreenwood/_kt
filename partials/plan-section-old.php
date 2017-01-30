<header>
	<h2>Plan Your Visit</h2>
</header>

<article class="map-box">

	<?php 
	
	// https://maps.googleapis.com/maps/api/place/radarsearch/json?location=54.326890,-2.747581&radius=5000&type=museum&key=AIzaSyDcE-HKUQ_hzqDNWic-jom-6Usv68xtd9I

	// 54.326890,-2.747581

	$gmTypes = array(
		array(
			'art_gallery',
			'Art Galleries',
		),
		array(
			'lodging',
			'Lodging & Acommodation'
		),
		array(
			'department_store',
			'Shopping'
		),
	);

	$gmTypeNameData = null;
	$gmTypeNameObj = null;
	$gmTypeNameInfo = null;
	$gmTypeNameInfoObj = null;
	
	//$local_art_gallery = get_remote_data('https://maps.googleapis.com/maps/api/place/radarsearch/json?location=54.326890,-2.747581&radius=3000&type=art_gallery&key=AIzaSyDcE-HKUQ_hzqDNWic-jom-6Usv68xtd9I');
	//$local_art_gallery_obj = json_decode($local_art_gallery);

	//$local_lodging = get_remote_data('https://maps.googleapis.com/maps/api/place/radarsearch/json?location=54.326890,-2.747581&radius=3000&type=lodging&key=AIzaSyDcE-HKUQ_hzqDNWic-jom-6Usv68xtd9I');
	//$local_lodging_obj = json_decode($local_lodging);

	//$local_department_stores = get_remote_data('https://maps.googleapis.com/maps/api/place/radarsearch/json?location=54.326890,-2.747581&radius=3000&type=department_store&key=AIzaSyDcE-HKUQ_hzqDNWic-jom-6Usv68xtd9I');
	//$local_department_stores_obj = json_decode($local_department_stores);

		foreach($gmTypes as $gmType):
			$gmTypeNameData = null;
			$gmTypeNameObj = null;
			
			$gmTypeName = $gmType[0];

			$gmTypeNameData = get_remote_data('https://maps.googleapis.com/maps/api/place/radarsearch/json?location=54.326890,-2.747581&radius=3000&type='.$gmTypeName.'&key=AIzaSyDcE-HKUQ_hzqDNWic-jom-6Usv68xtd9I');
			$gmTypeNameObj = json_decode($gmTypeNameData);

			foreach ($gmTypeNameObj->results as $mapRef):
				$gmTypeNameInfo = null;
				$gmTypeNameInfoObj = null;
			?>

			<div class="marker <?php echo $gmTypeName; ?>" data-type="<?php echo $gmTypeName; ?>" data-icon="<?php echo get_template_directory_uri(); ?>/assets/map-<?php echo $gmTypeName; ?>.png" data-lat="<?php echo $mapRef->geometry->location->lat; ?>" data-lng="<?php echo $mapRef->geometry->location->lng ?>">
				
				<?php
				$gmTypeNameInfo = get_remote_data('https://maps.googleapis.com/maps/api/place/details/json?placeid='.$mapRef->place_id.'&key=AIzaSyDcE-HKUQ_hzqDNWic-jom-6Usv68xtd9I');
				$gmTypeNameInfoObj = json_decode($gmTypeNameInfo);

				echo '<pre>';
					print_r($gmTypeNameInfoObj);
				echo '</pre>';

				?>

				<div class="info">
					<h2>
						<?php echo $gmTypeNameInfoObj->result->name; ?>
					</h2>
					<p>
						<?php 
						if(isset($gmTypeNameInfoObj->result->formatted_address)){
							echo $gmTypeNameInfoObj->result->formatted_address; 
						}
						?>
						<br>
						<?php 
						if(isset($gmTypeNameInfoObj->result->formatted_phone_number)){
							echo $gmTypeNameInfoObj->result->formatted_phone_number; 
						}
						?>
						<br>
						<?php 
						if(isset($gmTypeNameInfoObj->result->website)){
							echo $gmTypeNameInfoObj->result->website;
						}
						?>
					</p>
				</div>
			</div>

			<?php
			endforeach;
		endforeach;

	?>

	<div class="intro">

		<?php the_field('planning_intro', 'options'); ?>

		<h3>Make Your Selection</h3>

		<ul>

		<?php 
			foreach($gmTypes as $gmType):
			$gmTypeName = $gmType[0];
			$gmTypeDisplay = $gmType[1];
			
			?>
			<li>
				<span><?php echo $gmTypeDisplay; ?></span> <input id="<?php echo $gmTypeName; ?>" name="<?php echo $gmTypeName; ?>" value="<?php echo $gmTypeName; ?>" type="checkbox">
			</li>
			<?php 
			endforeach; 
		?>

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