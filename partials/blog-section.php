<div class="row">

	<header>
		<h2>From the Blog</h2>
	</header>

</div>

<div class="row">

	<div class="posts">
		<div class="item">
			<?php the_field( 'from_the_blog_intro' ); ?>
		</div>

		<?php $loop = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 5 ) ); ?>
		<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
		<div class="item">
		<a href="<?php the_permalink();?>"><?php the_post_thumbnail( 'hp-thumb' ); ?></a>
			<h2><a href="<?php the_permalink();?>"><?php the_title(); ?></a></h2>
			<?php the_excerpt(); ?>
			<a href="<?php the_permalink();?>">Read More...</a>
		</div>
		<?php endwhile; wp_reset_query(); ?>
	</div>
</div>