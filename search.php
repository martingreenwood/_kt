<?php
/**
 * The template for displaying search results pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package _kt
 */

get_header();

	/* Search Total Count */
	$allsearch = new WP_Query("s=$s&showposts=-1");
	$count = $allsearch->post_count;

	wp_reset_query();

	/* Number of posts per page and page number */
	$num_of_posts = 11;
	$pageNumber = (get_query_var('paged')) ? get_query_var('paged') : 1;

	/* Showing the lower post value */
	$n = ($pageNumber-1)*$num_of_posts;
	$n = $n+1;

	/* Showing the higher/highest post value */
	$m = $pageNumber * $num_of_posts;
	if($m > $count){
		// if m is bigger than the count var, it sets the
		// highest value equal to the count, this is for the last page of results
		$m = $count;
	}
	
	?>

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>


	<section id="header">
		<div class="container">
			<header class="page-header">
				<h1 class="page-title"><?php printf( esc_html__( 'You Searched For %s', '_kt' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
			</header>
		</div>
	</section>

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>

	<section id="counters">
		<div class="container">
			<div class="column">
				<?php
				if($count > 0){
				echo '
				Showing <strong>'.$n.'</strong>
				-<strong>'.$m.'</strong>
				of <strong>'.$count.'</strong>';
				}
				else{
				echo '<strong>No Results</strong>';
				} ?>
			</div>
			<div class="column">
				<?php wp_pagenavi(); ?>
			</div>
		</div>
	</section>

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>

	<section id="search">
		<div class="container">

			<div id="primary" class="content-area">
				<main id="main" class="site-main" role="main">

				<div id="searchcontiner">

				<article class="post">

					<?php the_field( 'search_blurb', 'options' ); ?>

				</article>

				<?php
				if ( have_posts() ) : 

					/* Start the Loop */
					while ( have_posts() ) : the_post();

						get_template_part( 'template-parts/content', 'search' );

					endwhile;

				else :

					//get_template_part( 'template-parts/content', 'none' );

				endif; ?>

				</div>

				</main>
			</div>

		</div>
	</section>

<?php
get_footer();
