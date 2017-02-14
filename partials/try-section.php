<div class="content">

	<header>
		<h2>Why Not Try</h2>
	</header>

</div>


<div class="similar row">

	<article>
		<?php the_field('why_not_try_text'); ?>
	</article>

	<?php
	$exclude_ids = array( $post->ID );
	$args = array(
		'post_type'      => 'page',
		'post__not_in' 	 => $exclude_ids,
		'posts_per_page' => -1,
		'post_parent'    => wp_get_post_parent_id( $post->ID ),
		'order'          => 'ASC',
		'orderby'        => 'menu_order'

	);
	$parent = new WP_Query( $args );
	if ( $parent->have_posts() ) : while ( $parent->have_posts() ) : $parent->the_post(); ?>
	<article id="parent-<?php the_ID(); ?>">
		<?php the_post_thumbnail( 'hp-thumb' ); ?>
		<h4><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h4>
		<p><?php echo excerpt('100'); ?></p>
		<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">Read More..</a>
	</article>

	<?php endwhile; endif; wp_reset_query(); ?>

</div>