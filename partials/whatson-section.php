<div class="content">

	<header>
		<h2>Events &amp; Festivals</h2>
	</header>

	<aside class="filters">
	<form method="get" accept-charset="utf-8" id="whatson_filter">
		<div class="colunn">
			<select name="genre" class="genre">
				<option value="">Genre</option>
			</select>
		</div>
		<div class="colunn">
			<input type="text" name="start" class="datepicker">
		</div>
		<div class="colunn">
			<input type="text" name="enddate" class="datepicker">
		</div>
	</form>
	</aside>


	<?php
		// url to get events from http://www.exploresouthlakeland.co.uk/exports/events/
		// this needs a date and passwoed set to the i= 
		// useing SHA1(PASSWORD-YYYYMMDD) defined in config.php DATENOW & PASSWORD & KTKEY

		echo KTKEY;

        // gets price list infor for a performace
		if(cached_and_valid(get_stylesheet_directory() . '/cache/events.txt')){
			$event_data = file_get_contents(get_stylesheet_directory() . '/cache/events.txt');
			$event_data_obj = json_decode($event_data);
		} else {
			$event_data = get_data('http://www.exploresouthlakeland.co.uk/exports/events/?i='.KTKEY);
			file_put_contents(get_stylesheet_directory() . '/cache/events.txt', $event_data);
			$event_data_obj = json_decode($event_data);
        }

	?>

	<pre>
		<?php print_r($event_data); ?>
	</pre>

</div>