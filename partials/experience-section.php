<header>
	<h2><?php the_field( 'social_wall_title' ); ?></h2>
</header>

<article class="grid">

	<?php juicer_feed('name=visitkendal'); ?>

	<div class="grad"></div>
	
</article>

<div class="more">
	<a href="<?php echo home_url( 'share-your-experiences' );?>">See More</a>
</div>
