<header>
	<h2>#KENDAL</h2>
</header>

<article class="grid">

<?php
    // Supply a user id and an access token
    $userid = '3886056307';
    $accessToken = '3886056307.1677ed0.eeaa6b9720b0453697f5313967a08ef8';

    $counter = 0;
   
    // Gets our data
    function fetchData($url){
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_TIMEOUT, 20);
         $result = curl_exec($ch);
         curl_close($ch); 
         return $result;
    }

    // Pulls and parses data.
    $result = fetchData("https://api.instagram.com/v1/users/{$userid}/media/recent/?access_token={$accessToken}");
    $result = json_decode($result);
?>

	<?php foreach ($result->data as $ig_post): $counter++?>
	<?php $ig_thumbnail = str_replace('s150x150/', 's320x320/', $ig_post->images->thumbnail->url); ?>
	<?php $ig_large 	= str_replace('s150x150/', 's640x640/', $ig_post->images->thumbnail->url); ?>
	<div class="ig-column">

		<?php if ($counter == 1): // if first ?>
			<div class="info top">
				<p>Lorem ipsum esterio dolor sit amet, consectetur adipicing elit etiam vitae. porta at, tristique elitas purus nulla, posuere acsia esi estibulum rutrum elit eros luctus.</p>

				<a href="https://www.instagram.com/kendalcumbria/" target="_blank">See more posts</a>
			</div>
		<?php endif; ?>
		
		<div class="ig-post">
			<div class="overlay">
				<div class="table"><div class="cell middle">
					<ul>
						<li>
							<a target="_blank" href="<?php echo $ig_post->link; ?>">
								<i class="fa fa-heart" aria-hidden="true"></i> <?php echo $ig_post->likes->count; ?>
							</a>
						</li>
						<li>
							<a target="_blank" href="<?php echo $ig_post->link; ?>">
								<i class="fa fa-comment" aria-hidden="true"></i> <?php echo $ig_post->comments->count; ?>
							</a>
						</li>
					</ul>
				</div></div>
			</div>
			<img src="<?php echo $ig_large; ?>" width="640" height="640" alt="">
		</div>

		<?php if ($counter == 3): // if first ?>
			<div class="info bottom">
				<h3>#KENDALCUMBRIA</h3>
				<p>Lorem ipsum esterio dolor sit amet, consectetur adipicing elit etiam vitae. porta at, tristique elitas purus nulla, posuere.</p>
			</div>
		<?php endif; ?>


	</div>
	<?php if ($counter == 3) break; ?>
	<?php endforeach ?> 
	
</article>