<div class="content">

	<?php
	// url to get events from http://www.exploresouthlakeland.co.uk/exports/clubs/
	// this needs a date and passwoed set to the i= 
	// using SHA1(PASSWORD-YYYYMMDD) defined in config.php DATENOW & PASSWORD & KTKEY

	if(cached_and_valid(FEED_CACHE . 'clubs.txt')){
		$clubs_data = file_get_contents(FEED_CACHE . 'clubs.txt');
		$clubs_data_obj = json_decode($clubs_data);
	} else {
		$clubs_data = get_data('http://www.exploresouthlakeland.co.uk/exports/clubs/?i='.KTKEY);
		file_put_contents(FEED_CACHE . 'clubs.txt', $clubs_data);
		$clubs_data_obj = json_decode($clubs_data);
    }

	if(cached_and_valid(FEED_CACHE . 'clubs_categories.txt')){
		$clubs_cat_data = file_get_contents(FEED_CACHE . 'clubs_categories.txt');
		$clubs_cat_obj = json_decode($clubs_cat_data);
	} else {
		$clubs_cat_data = get_data('http://www.exploresouthlakeland.co.uk/exports/clubs/categories//?i='.KTKEY);
		file_put_contents(FEED_CACHE . 'clubs_categories.txt', $clubs_cat_data);
		$clubs_cat_obj = json_decode($clubs_cat_data);
    }

	if(cached_and_valid(FEED_CACHE . 'clubs_towns.txt')){
		$clubs_towns_data = file_get_contents(FEED_CACHE . 'clubs_towns.txt');
		$clubs_towns_obj = json_decode($clubs_towns_data);
	} else {
		$clubs_towns_data = get_data('http://www.exploresouthlakeland.co.uk/exports/clubs/towns//?i='.KTKEY);
		file_put_contents(FEED_CACHE . 'clubs_towns.txt', $clubs_towns_data);
		$clubs_towns_obj = json_decode($clubs_towns_data);
    }

	?>

	<header>
		<h2>Clubs &amp; Societies</h2>
	</header>

	<aside class="filters">
	<form method="get" accept-charset="utf-8" id="whatson_filter">
		<div class="colunn">

			<?php
			foreach ($clubs_cat_obj as $clubs_cat) {
				if ($clubs_cat->category_status == 'Active') {
					$cats[] = $clubs_cat->category_name.'_'.$clubs_cat->category_id;
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
			foreach ($clubs_towns_obj as $clubs_towns) {				
				$towns[] = $clubs_towns->town_name.'_'.$clubs_towns->town_id;
			}
			$unique_towns = array_unique($towns);
			sort($unique_towns);
			$selected = null;
			?>
			&nbsp;
		</div>
		<div class="colunn">
			<input type="submit" name="submit" id="submit" value="Search">
		</div>
	</form>
	</aside>

	<div class="events event-list">
		<?php

		foreach ($clubs_data_obj as $clubs_data):
			
			$clubs_listing_cats = null;
			
			$club_id = $clubs_data->club_id;
			$club_name = $clubs_data->club_name;
			$club_description = $clubs_data->club_description;
			$club_address = $clubs_data->clubs_address;
			$club_address2 = $clubs_data->club_address2;
			$club_town_id = $clubs_data->club_town_id;
			$club_postcode = $clubs_data->club_postcode;
			$club_phone = $clubs_data->club_phone;
			$club_website = $clubs_data->club_website;
			$club_email = $clubs_data->club_email;
			$club_facebook = $clubs_data->club_facebook;
			$club_twitter = $clubs_data->club_twitter;
			$club_linkedin = $clubs_data->club_linkedin;
			$club_category_id = $clubs_data->club_category_id;
			$club_category_2_id = $clubs_data->club_category_2_id;
			$club_category_3_id = $clubs_data->club_category_3_id;
			$club_lat = $clubs_data->club_lat;
			$club_long = $clubs_data->club_long;
			$club_when = $clubs_data->club_when;
			$club_anyone = $clubs_data->club_anyone;
			$club_membership = $clubs_data->club_membership;
			$club_cost = $clubs_data->club_cost;
			$club_logo = $clubs_data->club_logo;

			foreach ($clubs_cat_obj as $clubs_cat) {
				if (
					$clubs_cat->category_id == $club_category_id 
					|| $clubs_cat->category_id == $club_category_2_id 
					|| $clubs_cat->category_id == $club_category_3_id 
					) {
					$club_listing_cats .= $clubs_cat->category_name . ", ";
				}
			}

			if ($club_town_id == 1):
				if (isset($_GET['cat'])):
					if ($_GET['cat'] == $club_category_id ||
						$_GET['cat'] == $club_category_2_id ||
						$_GET['cat'] == $directory_category_3_id ):
					?>
					
			<div class="event" data-id="<?php echo $club_id; ?>" data-town="<?php echo $club_town_id; ?>" data-cat="<?php echo $club_category_id; ?>" data-sec-cat="<?php echo $club_category_2_id; ?>" data-thi-cat="<?php echo $club_category_3_id; ?>">

				<?php
				$args = array(
					'post_type' => 'clubs',
					'meta_query'  => array(
						array(
							'key' => '_kendal_id',
							'value' => $club_id
						)
					)
				);

				$loop = new WP_Query( $args );
				?>

				<div class="info">
					<h3><?php echo $club_name; ?></h3>
					<p><?php echo $club_description; ?></p>
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
					endif; // end if cat matches
				
				else:
				?>


			<div class="event" data-id="<?php echo $club_id; ?>" data-town="<?php echo $club_town_id; ?>" data-cat="<?php echo $club_category_id; ?>" data-sec-cat="<?php echo $club_category_2_id; ?>" data-thi-cat="<?php echo $club_category_3_id; ?>">

				<?php
				$args = array(
					'post_type' => 'clubs',
					'meta_query'  => array(
						array(
							'key' => '_kendal_id',
							'value' => $club_id
						)
					)
				);

				$loop = new WP_Query( $args );
				?>

				<div class="info">
					<h3><?php echo $club_name; ?></h3>
					<p><?php echo $club_description; ?></p>
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
				endif;  // end if search set

			endif; // end check for town ID 1 (Kendal)
		endforeach; // end loop businesses
		?>
	</div>

</div>