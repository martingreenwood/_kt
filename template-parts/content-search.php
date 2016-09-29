<?php
/**
 * Template part for displaying results in search pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package _kt
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if(has_post_thumbnail(  )): ?>
		<?php the_post_thumbnail( 'hp-thumb' ); ?>
	<?php else: ?>
		<img src="http://placehold.it/410x275" alt="">
	<?php endif; ?>

	<header class="entry-header">
		<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
	</header>

	<div class="entry-summary">
		<p><?php echo excerpt('17'); ?></p>
	</div>

	<a href="<?php the_permalink(); ?>">Read More...</a>

</article>
