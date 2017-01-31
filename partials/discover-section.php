<header>
	<h2>Discover</h2>
</header>

<article class="grid">

	<div class="row">
		<div class="column">
			<aside class="text">
				<p><?php the_field( 'discover_intro' ); ?></p>
			</aside>

			<?php
			$feature_one = get_field('feature_one');
			$feature_oneID = get_post_thumbnail_id( $feature_one->ID );
			$feature_oneURL = wp_get_attachment_image_src( $feature_oneID, 'full' );
			?>
			<aside class="image" style="background-image: url(<?php echo $feature_oneURL[0]; ?>);">
				<a href="<?php echo get_permalink( $feature_one->ID ); ?>">
					<div class="table">
						<div class="cell bottom">
							<span><?php echo get_the_title( $feature_one->ID ); ?></span>
						</div>
					</div>
				</a>
			</aside>

			<?php
			$feature_two = get_field('feature_two');
			$feature_twoID = get_post_thumbnail_id( $feature_two->ID );
			$feature_twoURL = wp_get_attachment_image_src( $feature_twoID, 'full' );
			?>
			<aside class="image" style="background-image: url(<?php echo $feature_twoURL[0]; ?>);">
				<a href="<?php echo get_permalink( $feature_two->ID ); ?>">
					<div class="table">
						<div class="cell bottom">
							<span><?php echo get_the_title( $feature_two->ID ); ?></span>
						</div>
					</div>
				</a>
			</aside>

			<?php
			$feature_three = get_field('feature_three');
			$feature_threeID = get_post_thumbnail_id( $feature_three->ID );
			$feature_threeURL = wp_get_attachment_image_src( $feature_threeID, 'full' );
			?>
			<aside class="image" style="background-image: url(<?php echo $feature_threeURL[0]; ?>);">
				<a href="<?php echo get_permalink( $feature_three->ID ); ?>">
					<div class="table">
						<div class="cell bottom">
							<span><?php echo get_the_title( $feature_three->ID ); ?></span>
						</div>
					</div>
				</a>
			</aside>

		</div>
		<div class="column">
			<?php if(get_field( 'feature_four_video' )): ?>
			<?php
			$feature_four_video_thumbnail = get_field('feature_four_video_thumbnail');
			?>
			<aside class="image full" style="background-image: url(<?php echo $feature_four_video_thumbnail['url']; ?>);">
				<a href="#" class="playbid">
					&nbsp;
				</a>
				<div class="popupvid">
					<a class="close" href="#"></a>
					<div class="table">
						<div class="cell middle">
							<div class="embeder">
							<?php
							// get iframe HTML
							$iframe = get_field('feature_four_video_url');
							// use preg_match to find iframe src
							preg_match('/src="(.+?)"/', $iframe, $matches);
							$src = $matches[1];
							// add extra params to iframe src
							$params = array(
							    'controls'    => 0,
							    'hd'        => 1,
							    'autohide'    => 1
							);
							$new_src = add_query_arg($params, $src);
							$iframe = str_replace($src, $new_src, $iframe);
							// add extra attributes to iframe html
							$attributes = 'frameborder="0"';
							$iframe = str_replace('></iframe>', ' ' . $attributes . '></iframe>', $iframe);
							// echo $iframe
							echo $iframe;
							?>
							</div>
						</div>
					</div>
				</div>
			</aside>
			<?php else: ?>
			<?php
			$feature_four = get_field('feature_four');
			$feature_fourID = get_post_thumbnail_id( $feature_four->ID );
			$feature_fourURL = wp_get_attachment_image_src( $feature_fourID, 'full' );
			?>
			<aside class="image full" style="background-image: url(<?php echo $feature_fourURL[0]; ?>);">
				<a href="<?php echo get_permalink( $feature_four->ID ); ?>">
					<div class="table">
						<div class="cell bottom">
							<span><?php echo get_the_title( $feature_four->ID ); ?></span>
						</div>
					</div>
				</a>
			</aside>
			<?php endif; ?>
		</div>
	</div>

	<div class="row">
		<div class="column">
			<?php if(get_field( 'feature_five_video' )): ?>
			<?php
			$feature_five_video_thumbnail = get_field('feature_five_video_thumbnail');
			?>
			<aside class="image full" style="background-image: url(<?php echo $feature_five_video_thumbnail['url']; ?>);">
				<a href="#" class="playbid">
					&nbsp;
				</a>
				<div class="popupvid">
					<a class="close" href="#"></a>
					<div class="table">
						<div class="cell middle">
							<div class="embeder">
							<?php
							// get iframe HTML
							$iframe = get_field('feature_five_video_url');
							// use preg_match to find iframe src
							preg_match('/src="(.+?)"/', $iframe, $matches);
							$src = $matches[1];
							// add extra params to iframe src
							$params = array(
							    'controls'    => 0,
							    'hd'        => 1,
							    'autohide'    => 1
							);
							$new_src = add_query_arg($params, $src);
							$iframe = str_replace($src, $new_src, $iframe);
							// add extra attributes to iframe html
							$attributes = 'frameborder="0"';
							$iframe = str_replace('></iframe>', ' ' . $attributes . '></iframe>', $iframe);
							// echo $iframe
							echo $iframe;
							?>
							</div>
						</div>
					</div>
				</div>
			</aside>

			<?php else: ?>
			<?php
			$feature_five = get_field('feature_five');
			$feature_fiveID = get_post_thumbnail_id( $feature_five->ID );
			$feature_fiveURL = wp_get_attachment_image_src( $feature_fiveID, 'full' );
			?>
			<aside class="image full" style="background-image: url(<?php echo $feature_fiveURL[0]; ?>);">
				<a href="<?php echo get_permalink( $feature_five->ID ); ?>">
					<div class="table">
						<div class="cell bottom">
							<span><?php echo get_the_title( $feature_five->ID ); ?></span>
						</div>
					</div>
				</a>
			</aside>
			<?php endif; ?>
		</div>
		<div class="column">
			<div class="half">
				<?php
				$feature_six = get_field('feature_six');
				$feature_sixID = get_post_thumbnail_id( $feature_six->ID );
				$feature_sixURL = wp_get_attachment_image_src( $feature_sixID, 'full' );
				?>
				<aside class="image" style="background-image: url(<?php echo $feature_sixURL[0]; ?>);">
					<a href="<?php echo get_permalink( $feature_six->ID ); ?>">
						<div class="table">
							<div class="cell bottom">
								<span><?php echo get_the_title( $feature_six->ID ); ?></span>
							</div>
						</div>
					</a>
				</aside>

				<?php
				$feature_seven = get_field('feature_seven');
				$feature_sevenID = get_post_thumbnail_id( $feature_seven->ID );
				$feature_sevenURL = wp_get_attachment_image_src( $feature_sevenID, 'full' );
				?>
				<aside class="image" style="background-image: url(<?php echo $feature_sevenURL[0]; ?>);">
					<a href="<?php echo get_permalink( $feature_seven->ID ); ?>">
						<div class="table">
							<div class="cell bottom">
								<span><?php echo get_the_title( $feature_seven->ID ); ?></span>
							</div>
						</div>
					</a>
				</aside>
			</div>
	
			<div class="half">
				<?php
				$feature_eight = get_field('feature_eight');
				$feature_eightID = get_post_thumbnail_id( $feature_eight->ID );
				$feature_eightURL = wp_get_attachment_image_src( $feature_eightID, 'full' );
				?>
				<aside class="image" style="background-image: url(<?php echo $feature_eightURL[0]; ?>);">
					<a href="<?php echo get_permalink( $feature_eight->ID ); ?>">
						<div class="table">
							<div class="cell bottom">
								<span><?php echo get_the_title( $feature_eight->ID ); ?></span>
							</div>
						</div>
					</a>
				</aside>

				
				<aside class="text">
					<?php the_field('feature_text'); ?>
				</aside>
			</div>
		</div>
	</div>

	
</article>