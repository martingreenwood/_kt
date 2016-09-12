<div class="row">

	<header>
		<h2>From the Blog</h2>
	</header>

</div>

<div class="row">

	<div class="posts">
		<div class="item">
			<p>Lorem ipsum dolor sitet eriasa amet, consectetur adipiscing elit aliqua pretium justo id leo ornare, ac varius eros consectetur ut hendre ritamol metus id aliquet. </p>

			<p>Vestibulum lorem es lorem, vehicula id fermentum maximus, ornare at lectus suspendise aies pellentesque, libero nec euismod auctor, lorem ligula aliquet nibh.</p>
		</div>

		<?php $loop = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 5 ) ); ?>
		<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
		<div class="item">
		<?php the_post_thumbnail( 'hp-thumb' ); ?>
			<h2><?php the_title(); ?></h2>
			<?php the_excerpt(); ?>
			<a href="<?php the_permalink();?>">Read More...</a>
		</div>
		<?php endwhile; wp_reset_query(); ?>
	</div>
</div>