<div class="content">

	<?php
	// url to get events from http://www.exploresouthlakeland.co.uk/exports/events/
	// this needs a date and passwoed set to the i= 
	// useing SHA1(PASSWORD-YYYYMMDD) defined in config.php DATENOW & PASSWORD & KTKEY

	if(cached_and_valid(FEED_CACHE . 'business.txt')){
		$business_data = file_get_contents(FEED_CACHE . 'business.txt');
		$business_data_obj = json_decode($business_data);
	} else {
		$business_data = get_data('http://www.exploresouthlakeland.co.uk/exports/business/?i='.KTKEY);
		file_put_contents(FEED_CACHE . 'business.txt', $business_data);
		$business_data_obj = json_decode($business_data);
    }

	if(cached_and_valid(FEED_CACHE . 'business_categories.txt')){
		$business_cat_data = file_get_contents(FEED_CACHE . 'business_categories.txt');
		$business_cat_obj = json_decode($business_cat_data);
	} else {
		$business_cat_data = get_data('http://www.exploresouthlakeland.co.uk/exports/business/categories//?i='.KTKEY);
		file_put_contents(FEED_CACHE . 'business_categories.txt', $business_cat_data);
		$business_cat_obj = json_decode($business_cat_data);
    }

	if(cached_and_valid(FEED_CACHE . 'business_towns.txt')){
		$business_towns_data = file_get_contents(FEED_CACHE . 'business_towns.txt');
		$business_towns_obj = json_decode($business_towns_data);
	} else {
		$business_towns_data = get_data('http://www.exploresouthlakeland.co.uk/exports/business/towns//?i='.KTKEY);
		file_put_contents(FEED_CACHE . 'business_towns.txt', $business_towns_data);
		$business_towns_obj = json_decode($business_towns_data);
    }

	?>

	<header>
		<h2>Business Directory</h2>
	</header>

	<aside class="filters">
	<form method="get" accept-charset="utf-8" id="whatson_filter">
		<div class="colunn">

		<input type="hidden" name="datepicker" id="datepicker">

			<?php
			foreach ($business_cat_obj as $business_cat) {
				if ($business_cat->category_status == 'Active') {
					$cats[] = $business_cat->category_name.'_'.$business_cat->category_id;
				}
			}
			$unique_cats = array_unique($cats);
			sort($unique_cats);
			$selected = null;
			?>
			<select name="cat" class="cat">
				<option value="">Category</option>
				<?php
				foreach ($unique_cats as $unique_cat) {
					$cat = explode("_", $unique_cat);
					if ($_GET['cat'] == $cat[1]):
						$selected = 'selected';
					else:
						$selected = '';
					endif;
					echo '<option '.$selected.' value="'.$cat[1].'">'.$cat[0].'</option>';
				}
				?>
			</select>
		</div>

		<div class="colunn">
			<?php
			foreach ($business_towns_obj as $business_town) {				
				$towns[] = $business_town->town_name.'_'.$business_town->town_id;
			}
			$unique_towns = array_unique($towns);
			sort($unique_towns);
			$selected = null;
			?>
			<select name="town" class="town">
				<option value="">Town</option>
				<?php
				foreach ($unique_towns as $unique_town) {
					$town = explode("_", $unique_town);
					if ($_GET['town'] == $town[1]):
						$selected = 'selected';
					else:
						$selected = '';
					endif;
					echo '<option '.$selected.' value="'.$town[1].'">'.$town[0].'</option>';
				}
				?>
			</select>
		</div>
		<div class="colunn">
			<input type="submit" name="submit" id="submit" value="Search">
		</div>
	</form>
	</aside>

	<div class="events event-list">
		<?php
		// url to get events from http://www.exploresouthlakeland.co.uk/exports/business/
		// this needs a date and passwoed set to the i= 
		// useing SHA1(PASSWORD-YYYYMMDD) defined in config.php DATENOW & PASSWORD & KTKEY

		foreach ($business_data_obj as $business_data):
			$directory_listing_cats = null;
			
			$directory_id = $business_data->directory_id;
			$directory_name = $business_data->directory_name;
			$directory_description = $business_data->directory_description;
			$directory_address = $business_data->directory_address;
			$directory_address2 = $business_data->directory_address2;
			$directory_town_id = $business_data->directory_town_id;
			$directory_postcode = $business_data->directory_postcode;
			$directory_phone = $business_data->directory_phone;
			$directory_website = $business_data->directory_website;
			$directory_email = $business_data->directory_email;
			$directory_facebook = $business_data->directory_facebook;
			$directory_twitter = $business_data->directory_twitter;
			$directory_linkedin = $business_data->directory_linkedin;
			$directory_category_id = $business_data->directory_category_id;
			$directory_category_2_id = $business_data->directory_category_2_id;
			$directory_category_3_id = $business_data->directory_category_3_id;
			$directory_lat = $business_data->directory_lat;
			$directory_long = $business_data->directory_long;

			foreach ($business_cat_obj as $business_cat) {
				if (
					$business_cat->category_id == $directory_category_id 
					|| $business_cat->category_id == $directory_category_2_id 
					|| $business_cat->category_id == $directory_category_3_id 
					) {
					$directory_listing_cats .= $business_cat->category_name . ", ";
				}
			}

			?>

		<div class="event" data-id="<?php echo $directory_id; ?>" data-town="<?php echo $directory_town_id; ?>" data-cat="<?php echo $directory_category_id; ?>" data-sec-cat="<?php echo $directory_category_2_id; ?>" data-thi-cat="<?php echo $directory_category_3_id; ?>">

			<!--<div class="img">
			<?php
			$args = array(
				'post_type' => 'businesses',
				'meta_query'  => array(
					array(
						'key' => '_kendal_id',
						'value' => $directory_id
					)
				)
			);

			$loop = new WP_Query( $args );
			if ($loop->have_posts()) {
				while ( $loop->have_posts() ) : $loop->the_post(); 
					the_post_thumbnail("600x430");
				endwhile; 
			} else {
				echo '<img src="//lorempixel.com/600/430/abstract">';
			}
			?>
			</div>-->

			<div class="meta">
				<small><?php echo substr($directory_listing_cats, 0, -2); ?></small>
			</div>

			<div class="info">
				<h3><?php echo $directory_name; ?></h3>
				<p><?php echo $directory_description; ?></p>
				<?php
				if ($loop->have_posts()) {
					while ( $loop->have_posts() ) : $loop->the_post(); 
						echo '<a href="'.get_permalink().'">read more...</a>';
					endwhile; 
				} else {
					
				}
				?>
			</div>

		</div>

		<?php 
		endforeach;
		?>
	</div>

</div>