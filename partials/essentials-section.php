<div class="content">

	<header>
		<h2>Essentials</h2>
	</header>

</div>


<div class="grid row">

	<article class="intro">
		<?php the_field('essentials_intro'); ?>
	</article>

	<?php 
	if( have_rows('essentials_grid') ):
	while ( have_rows('essentials_grid') ) : the_row();
	?>

	<article>
		<?php $image = get_sub_field('image'); ?>
		<img src="<?php echo $image['sizes']['hp-thumb'];  ?>" alt="">
		<h4><a href="<?php the_sub_field('link'); ?>" title="<?php the_sub_field('heading'); ?>"><?php the_sub_field('heading'); ?></a></h4>
		<?php the_sub_field('intro'); ?>
		<a href="<?php the_sub_field('link'); ?>" title="<?php the_sub_field('heading'); ?>">Read More..</a>
	</article>

	<?php 
	endwhile;
	endif;
	?>

</div>