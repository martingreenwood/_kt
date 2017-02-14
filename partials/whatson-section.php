<div class="content">

	<?php
	// url to get events from http://www.exploresouthlakeland.co.uk/exports/events/
	// this needs a date and passwoed set to the i= 
	// useing SHA1(PASSWORD-YYYYMMDD) defined in config.php DATENOW & PASSWORD & KTKEY

	if(cached_and_valid(FEED_CACHE . 'events.txt')){
		$event_data = file_get_contents(FEED_CACHE . 'events.txt');
		$event_data_obj = json_decode($event_data);
	} else {
		$event_data = get_data('http://www.exploresouthlakeland.co.uk/exports/events/?i='.KTKEY);
		file_put_contents(FEED_CACHE . 'events.txt', $event_data);
		$event_data_obj = json_decode($event_data);
    }

	?>

	<header>
		<h2>What's On</h2>
	</header>

	<aside class="filters">
	<form method="get" accept-charset="utf-8" id="whatson_filter" action="#whatson">
		<div class="colunn">
			<?php 
			foreach ($event_data_obj as $event_data) {
				$types[] = $event_data->type.'_'.$event_data->type_id;
			}
			$unique_types = array_unique($types);
			sort($unique_types)
			?>
			<select name="genre" class="genre">
				<option value="">Genre</option>
				<?php
				foreach ($unique_types as $event_type) {
					$type = explode("_", $event_type);
					if (isset($_GET['genre']) == $type[1]):
						$selected = 'selected';
					else:
						$selected = '';
					endif;
					echo '<option value="'.$type[1].'">'.$type[0].'</option>';
				}
				?>
			</select>
		</div>
		<div class="colunn">
			<input type="text" name="start" id="datepicker" class="datepicker" placeholder="Select Date">
		</div>
		<div class="colunn">
			<input type="submit" name="submit" id="submit" value="Search">
		</div>
	</form>
	</aside>

	<div class="search-error">
		<span>Sorry, we couldn't find anything to match your search.</span>
	</div>

	<div class="events event-list">
		<?php
		// url to get events from http://www.exploresouthlakeland.co.uk/exports/events/
		// this needs a date and passwoed set to the i= 
		// useing SHA1(PASSWORD-YYYYMMDD) defined in config.php DATENOW & PASSWORD & KTKEY

		function cmp($a, $b)
		{
			return strcmp($a->start_date, $b->start_date);
		}

		usort($event_data_obj, "cmp");

		foreach ($event_data_obj as $event_data):
			$id = $event_data->id;
			$name = $event_data->name;
			$type_id = $event_data->type_id;
			$type_id_2 = $event_data->type_id_2;
			$type = $event_data->type;
			$town = $event_data->town;
			//$area = $event_data->area;
			//$location = $event_data->location;
			//$postcode = $event_data->postcode;
			$start_date = $event_data->start_date;
			$end_date = $event_data->end_date;
			$time = $event_data->time;
			$description = $event_data->description;
			$min_price = $event_data->min_price;
			$max_price = $event_data->max_price;
			//$contact_name = $event_data->contact_name;
			//$contact_address = $event_data->contact_address;
			//$contact_tel = $event_data->contact_tel;
			//$email = $event_data->email;
			//$url = $event_data->url;
			//$featured = $event_data->featured;

			if ( $min_price && $max_price ) {
				
				if ( $min_price == 'N/A' && $max_price == 'N/A' ) {
					$price = null;
				} elseif ( $min_price == 'N/A' || $min_price == '.' && $max_price != 'N/A' ) {
					$price = $max_price;
				} elseif ( $min_price != 'N/A' && $max_price == 'N/A' ) {
					$price = $min_price;
				} elseif ( $min_price == $max_price ) {
					$price = $max_price;
				} else {
					$price = $min_price ." - ". $max_price;
				}
			}

			$start_date = date("d-m-Y", strtotime($start_date));
			$end_date = date("d-m-Y", strtotime($start_date));

			$e_start_time = strtotime($start_date);
			$e_end_time = strtotime($end_date);
			
			$enddatemonth = date('d-m-Y', strtotime("+1 months", strtotime($start_date)));
			$now = time('now');

			// if events are now or inf the future
			if ( $e_start_time > $now ):
				if ($town == "Kendal"):
					if (isset($_GET['genre'])):
						if ($_GET['genre'] == $type_id ||
							$_GET['genre'] == $type_id_2 &&
							$_GET['start'] >= $start_date &&
							$_GET['start'] <= $enddatemonth ):

			?>

					<div class="event" data-min-price="<?php echo $min_price; ?>" data-max-price="<?php echo $max_price; ?>" data-id="<?php echo $id; ?>" data-type="<?php echo $type_id; ?>" data-start="<?php echo $start_date; ?>" data-end="<?php echo $end_date; ?>">

						<div class="info">
							<h3><?php echo $name; ?></h3>
							<div class="meta" style="color: #999;">
								<ul>
									<li><?php echo date("D dS M Y", $e_start_time); ?></li>
									<?php if ($price): ?>
									<li class="price"><?php echo $price; ?></li>
									<?php endif; ?>
								</ul>
								<div class="clear"></div>
							</div>
							<p><?php echo substr($description, 0, 150); ?>...</p>

							<a target="_blank" href="http://www.exploresouthlakeland.co.uk/events/info.php?id=<?php echo $id; ?>">Read More <sup><i class="fa fa-external-link" aria-hidden="true"></i></sup></a>
						</div>

					</div>

						<?php 
						endif; 
					else:
					?>

					<div class="event" data-min-price="<?php echo $min_price; ?>" data-max-price="<?php echo $max_price; ?>" data-id="<?php echo $id; ?>" data-type="<?php echo $type_id; ?>" data-start="<?php echo $start_date; ?>" data-end="<?php echo $end_date; ?>">

						<div class="info">
							<h3><?php echo $name; ?></h3>
							<div class="meta" style="color: #999;">
								<ul>
									<li><?php echo date("D dS M Y", $e_start_time); ?></li>
									<?php if ($price): ?>
									<li class="price"><?php echo $price; ?></li>
									<?php endif; ?>
								</ul>
								<div class="clear"></div>
							</div>
							<p><?php echo substr($description, 0, 150); ?>...</p>

							<a target="_blank" href="http://www.exploresouthlakeland.co.uk/events/info.php?id=<?php echo $id; ?>">Read More <sup><i class="fa fa-external-link" aria-hidden="true"></i></sup></a>
						</div>

					</div>

					<?php 
					endif; // end if genre
				endif; // end of kendal
			endif; // end if in future
		endforeach;
		?>
	</div>

</div>