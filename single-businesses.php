<?php
/**
 * The template for displaying all single businesses.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package _kt
 */

// url to get events from http://www.exploresouthlakeland.co.uk/exports/events/
// this needs a date and passwoed set to the i= 
// useing SHA1(PASSWORD-YYYYMMDD) defined in config.php DATENOW & PASSWORD & KTKEY

if(cached_and_valid(get_stylesheet_directory() . '/cache/business.txt')){
	$business_data = file_get_contents(get_stylesheet_directory() . '/cache/business.txt');
	$business_data_obj = json_decode($business_data);
} else {
	$business_data = get_data('http://www.exploresouthlakeland.co.uk/exports/business/?i='.KTKEY);
	file_put_contents(get_stylesheet_directory() . '/cache/business.txt', $business_data);
	$business_data_obj = json_decode($business_data);
}

if(cached_and_valid(get_stylesheet_directory() . '/cache/business_categories.txt')){
	$business_cat_data = file_get_contents(get_stylesheet_directory() . '/cache/business_categories.txt');
	$business_cat_obj = json_decode($business_cat_data);
} else {
	$business_cat_data = get_data('http://www.exploresouthlakeland.co.uk/exports/business/categories//?i='.KTKEY);
	file_put_contents(get_stylesheet_directory() . '/cache/business_categories.txt', $business_cat_data);
	$business_cat_obj = json_decode($business_cat_data);
}

if(cached_and_valid(get_stylesheet_directory() . '/cache/business_towns.txt')){
	$business_towns_data = file_get_contents(get_stylesheet_directory() . '/cache/business_towns.txt');
	$business_towns_obj = json_decode($business_towns_data);
} else {
	$business_towns_data = get_data('http://www.exploresouthlakeland.co.uk/exports/business/towns//?i='.KTKEY);
	file_put_contents(get_stylesheet_directory() . '/cache/business_towns.txt', $business_towns_data);
	$business_towns_obj = json_decode($business_towns_data);
}

// url to get events from http://www.exploresouthlakeland.co.uk/exports/events/
// this needs a date and passwoed set to the i= 
// useing SHA1(PASSWORD-YYYYMMDD) defined in config.php DATENOW & PASSWORD & KTKEY

foreach ($business_data_obj as $business_data):
	if( get_post_meta($post->ID, '_kendal_id', true) == $business_data->directory_id ):
		
		$directory_listing_cats = null;
		$directory_town = null;
		
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

		foreach ($business_towns_obj as $business_towns) {
			if ( $business_towns->town_id == $directory_town_id ) {
				$directory_town .= $business_towns->town_name;
			}
		}
		
	endif; // end if ID Matched
endforeach; 

get_header(); ?>

	<section class="slider">
		<?php the_post_thumbnail('169cover'); ?>
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

		<div class="row">
			<div class="column">
				<div class="entry-content">
					<p><?php echo $directory_description; ?></p>
				</div>
			</div>
		</div>

		<main id="main" class="site-main row" role="main">
				
			<div class="column two-thirds">

				<?php 
				if( have_rows('cr_repeater') ): while ( have_rows('cr_repeater') ) : the_row(); 
					?>
					<article class="row">

					<?php 
					$image = get_sub_field('cr_image'); $size = 'full';
					if( $image ) {
					echo wp_get_attachment_image( $image, $size );
					}
					?>

			        <div class="caption">
			        	<?php the_sub_field('cr_caption'); ?> 
			        </div>

			        <div class="copy">
			        	<?php the_sub_field('cr_content'); ?>
			        </div>

			        </article>
			       <?php
			    endwhile; endif; 
				?>

			</div>

			<aside class="thirds">

				<div class="open">
					<h3>Times &amp; Dates</h3>
					<?php the_field('opening_times'); ?>

					<h3>Categories</h3>
					<p><?php echo substr($directory_listing_cats, 0, -2); ?></p>

					<h3>Contact Info</h3>
					<p>
					<?php if ($directory_address): ?>
						<?php echo $directory_address; ?><br>
						<?php echo $directory_address2; ?><br>
						<?php echo $directory_town; ?><br>
						<?php echo $directory_postcode; ?><br>
					<?php endif; ?>
					<?php echo $directory_phone; ?><br>
					<?php echo $directory_email; ?><br>
					<?php echo $directory_address; ?><br>
					</p>

					<p>
					<?php if($directory_facebook): ?><i class="fa fa-facebook" aria-hidden="true"></i> <?php echo $directory_facebook; ?><br><?php endif; ?>
					<?php if($directory_twitter): ?><i class="fa fa-twitter" aria-hidden="true"></i> <?php echo $directory_twitter; ?><br><?php endif; ?>
					<?php if($directory_linkedin): ?><i class="fa fa-linkedin" aria-hidden="true"></i> <?php echo $directory_linkedin; ?><br><?php endif; ?>
					</p>
				</div>

				<div class="location">
					<h3>GET DIRECTIONS</h3>
					<?php  
					$location = get_field('location');
					if ($directory_lat && $directory_long): 
					?>
					<div class="map">
						<div class="marker" data-lat="<?php echo $directory_lat; ?>" data-lng="<?php echo $directory_long; ?>"></div>
					</div>					
					<?php elseif( !empty($location) ): ?>
					<div class="map">
						<div class="marker" data-lat="<?php echo $location['lat']; ?>" data-lng="<?php echo $location['lng']; ?>"></div>
					</div>
					<?php endif; ?>
				</div>

				<div class="website">
					<h3>More Info / How to Book</h3>
					<?php if (isset($directory_website)): ?>
					<a href="<?php echo $directory_website; ?>">Visit Website</a>
					<?php else: ?>
					<a href="<?php the_field('website'); ?>">Visit Website</a>
					<?php endif; ?>
				</div>
				
			</aside>


		</main>
	</div>

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>

	<section id="share">
		<div class="container">

		<?php get_template_part( 'partials/share', 'section' ); ?>

		</div>
	</section>

<?php
get_footer();
