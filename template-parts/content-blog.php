<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package _kt
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php
		the_title( '<h2>', '</h2>' );
		if ( 'post' === get_post_type() ) : ?>
		<div class="entry-meta">
			<p>Posted on <?php the_date( ); ?></p>
		</div><!-- .entry-meta -->
		<?php
		endif; ?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php
			the_excerpt();
		?>
	</div><!-- .entry-content -->

	<div class="link">
		<a class="more" href="<?php echo the_permalink(); ?>">Read More</a>		
	</div>

</article><!-- #post-## -->
