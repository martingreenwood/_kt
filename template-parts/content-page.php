<?php
/**
 * Template part for displaying page content in page.php.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package _kt
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">
		<?php
			the_content();
		?>

		<!-- Begin MailChimp Signup Form -->
		<div id="mc_embed_signup">
			<form action="//visit-kendal.us14.list-manage.com/subscribe/post?u=3faa5797fd0c077009782bc4e&amp;id=d4f0b2d1d3" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
				<div id="mc_embed_signup_scroll">

					<input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="Email address" required>
					<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_3faa5797fd0c077009782bc4e_d4f0b2d1d3" tabindex="-1" value=""></div>
					<input type="submit" value="Sign-up" name="subscribe" id="mc-embedded-subscribe" class="button">
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
			</form>
			<div class="clear"></div>
		</div>
		<!--End mc_embed_signup-->

		<p>Sign up to hear first when we launch our new website - full of everything that makes Kendal special.</p>

	</div>

</article>
