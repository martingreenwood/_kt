<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package _kt
 */

get_header(); ?>

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
			<h1 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', '_kt' ); ?></h1>
		</div>
	</section>

	<div id="primary" class="content-area container">

		<main id="main" class="site-main row" role="main">
				
			<div class="column">

				<section class="error-404 not-found">

					<div class="page-content">

					</div><!-- .page-content -->

				</section><!-- .error-404 -->

			</div>

		</main>
	</div>

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>

	<section id="signup">
		<div class="container">

		<?php get_template_part( 'partials/signup', 'section' ); ?>

		</div>
	</section>

	<section class="break">
		<div class="container">
			<hr>
		</div>
	</section>

	<section id="blog">
		<div class="container">

		<?php get_template_part( 'partials/blog', 'section' ); ?>

		</div>
	</section>

<?php
get_footer();
