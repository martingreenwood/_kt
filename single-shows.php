<?php
/**
 * The template for displaying all single shows.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package _kt
 */

// url to get events from http://www.exploresouthlakeland.co.uk/exports/events/
// this needs a date and passwoed set to the i= 
// useing SHA1(PASSWORD-YYYYMMDD) defined in config.php DATENOW & PASSWORD & KTKEY

if(cached_and_valid(get_stylesheet_directory() . '/cache/events.txt')){
	$event_data = file_get_contents(get_stylesheet_directory() . '/cache/events.txt');
	$event_data_obj = json_decode($event_data);
} else {
	$event_data = get_data('http://www.exploresouthlakeland.co.uk/exports/events/?i='.KTKEY);
	file_put_contents(get_stylesheet_directory() . '/cache/events.txt', $event_data);
	$event_data_obj = json_decode($event_data);
}

// url to get events from http://www.exploresouthlakeland.co.uk/exports/events/
// this needs a date and passwoed set to the i= 
// useing SHA1(PASSWORD-YYYYMMDD) defined in config.php DATENOW & PASSWORD & KTKEY

foreach ($event_data_obj as $event_data):
	if( get_post_meta($post->ID, '_kendal_id', true) == $event_data->id ):
		
		$id = $event_data->id;
		$name = $event_data->name;
		$type_id = $event_data->type_id;
		$type_id_2 = $event_data->type_id_2;
		$type = $event_data->type;
		$town = $event_data->town;
		$area = $event_data->area;
		$location = $event_data->location;
		$postcode = $event_data->postcode;
		$start_date = $event_data->start_date;
		$end_date = $event_data->end_date;
		$time = $event_data->time;
		$description = $event_data->description;
		$min_price = $event_data->min_price;
		$max_price = $event_data->max_price;
		$contact_name = $event_data->contact_name;
		$contact_address = $event_data->contact_address;
		$contact_tel = $event_data->contact_tel;
		$email = $event_data->email;
		$url = $event_data->url;
		$featured = $event_data->featured;

		if ( $min_price && $max_price ) {
			
			if ( $min_price == 'N/A' && $max_price == 'N/A' ) {
				$price = null;
			} elseif ( $min_price == 'N/A' || $min_price == '.' && $max_price != 'N/A' ) {
				$price = $max_price;
			} elseif ( $min_price != 'N/A' && $max_price == 'N/A' ) {
				$price = $min_price;
			} elseif ( $min_price == $max_price ) {
				$price = $max_price;
			} else {
				$price = $min_price ." - ". $max_price;
			}
		}

		$start_date = date("d-m-Y", strtotime($start_date));
		$end_date = date("d-m-Y", strtotime($end_date));

		$e_start_time = strtotime($start_date);
		$e_end_time = strtotime($end_date);
		$now = time('now');
		
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
					<p><?php echo $description; ?></p>
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
					<h3>Genre</h3>
					<p><?php echo $type; ?></p>
					
					<h3><?php if ( $e_start_time > $now ): ?>Showing<?php else: ?>Showed<?php endif; ?></h3>
					<p><?php echo date("D dS M Y", $e_start_time); ?></p>
					
					<h3>Tickets / Entry</h3>
					<?php if ($price): ?>
					<p><?php echo $price; ?></p>
					<?php else: ?>
					<p>FREE</p>
					<?php endif; ?>

				</div>

				<div class="location">
					<h3>GET DIRECTIONS</h3>
					<?php  $location = get_field('location');
					if( !empty($location) ):
					?>
					<div class="map">
						<div class="marker" data-lat="<?php echo $location['lat']; ?>" data-lng="<?php echo $location['lng']; ?>"></div>
					</div>
					<?php endif; ?>
				</div>

				<div class="website">
					<h3>More Info / How to Book</h3>
					<a href="<?php the_field('website'); ?>">Visit Website</a>
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
