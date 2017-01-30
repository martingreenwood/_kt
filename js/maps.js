(function($) {

	/*
	*  new_map
	*
	*  This function will render a Google Map onto the selected jQuery element
	*
	*  @type	function
	*  @date	8/11/2013
	*  @since	4.3.0
	*
	*  @param	$el (jQuery element)
	*  @return	n/a
	*/

	function new_map( $el ) {
		
		// var
		var $markers = $el.find('.marker');
		
		
		// vars
		var args = {
			zoom		: 12,
			center		: new google.maps.LatLng(0, 0),
			mapTypeId	: google.maps.MapTypeId.ROADMAP,
			scrollwheel : false,
		};
		
		
		// create map	        	
		var map = new google.maps.Map( $el[0], args);
		
		
		// add a markers reference
		map.markers = [];
		
		
		// add markers
		$markers.each(function(){
			
	    	add_marker( $(this), map );
			
		});
		
		
		// center map
		center_map( map );
		
		
		// return
		return map;
		
	}

	/*
	*  add_marker
	*
	*  This function will add a marker to the selected Google Map
	*
	*  @type	function
	*  @date	8/11/2013
	*  @since	4.3.0
	*
	*  @param	$marker (jQuery element)
	*  @param	map (Google Map object)
	*  @return	n/a
	*/

	function add_marker( $marker, map ) {

		// var
		var latlng = new google.maps.LatLng( $marker.attr('data-lat'), $marker.attr('data-lng') );

		var zoom = $marker.attr('data-icon');

		var icon = {
			url: $marker.attr('data-icon'), // url
			size: new google.maps.Size(32, 32),     // original size you defined in the SVG file
			scaledSize: new google.maps.Size(32, 32), // scaled size
			//origin: new google.maps.Point(0,0), // origin
			//anchor: new google.maps.Point(0, 0) // anchor
		}

		// create marker
		var marker = new google.maps.Marker({
			position	: latlng,
			map			: map,
			icon		: icon
		});
		marker.setVisible(false);

		// add to array
		map.markers.push( marker );

		// if marker contains HTML, add it to an infoWindow
		if( $marker.html() )
		{
			// create info window
			var infowindow = new google.maps.InfoWindow({
				content		: $marker.html()
			});

			// show info window when marker is mouseover
			google.maps.event.addListener(marker, 'mouseover', function() {
				infowindow.open( map, marker );
			});
			google.maps.event.addListener(marker, 'mouseout', function() {
				infowindow.close( map, marker );
			});

		}

		google.maps.event.addListener(marker, 'art_gallery', function () {
			if($marker.hasClass($('input[name="art_gallery"]:checked').val())) {
				marker.setVisible(true);
			} else {
				marker.setVisible(false);
			}
		});
		google.maps.event.addListener(marker, 'lodging', function () {
			if($marker.hasClass($('input[name="lodging"]:checked').val())) {
				marker.setVisible(true);
			} else {
				marker.setVisible(false);
			}
		});
		google.maps.event.addListener(marker, 'department_store', function () {
			if($marker.hasClass($('input[name="department_store"]:checked').val())) {
				marker.setVisible(true);
			} else {
				marker.setVisible(false);
			}
		});
		
		$('input[name="art_gallery"]').change(function() {
			google.maps.event.trigger(marker, 'art_gallery');
		});
		$('input[name="lodging"]').change(function() {
			google.maps.event.trigger(marker, 'lodging');
		});
		$('input[name="department_store"]').change(function() {
			google.maps.event.trigger(marker, 'department_store');
		});

	}

	/*
	*  center_map
	*
	*  This function will center the map, showing all markers attached to this map
	*
	*  @type	function
	*  @date	8/11/2013
	*  @since	4.3.0
	*
	*  @param	map (Google Map object)
	*  @return	n/a
	*/

	function center_map( map ) {

		// vars
		var bounds = new google.maps.LatLngBounds();

		// loop through all markers and create bounds
		$.each( map.markers, function( i, marker ){

			var latlng = new google.maps.LatLng( marker.position.lat(), marker.position.lng() );

			bounds.extend( latlng );

		});

		// only 1 marker?
		if( map.markers.length == 1 )
		{
			// set center of map
		    map.setCenter( bounds.getCenter() );
		    map.setZoom( 18 );
		}
		else
		{
			// fit to bounds
			map.fitBounds( bounds );
		}

	}

	/*
	*  document ready
	*
	*  This function will render each map when the document is ready (page has loaded)
	*
	*  @type	function
	*  @date	8/11/2013
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	// global var
	var map = null;

	$(document).ready(function(){

		$('.map').each(function(){

			// create map
			map = new_map( $(this) );

			if ($('.map:visible').length) {
				$('dd a').click(function(){      
				var info = $(this).attr('rel');
				var substr = info.split(',');
				map.panTo(new google.maps.LatLng(substr[0], substr[1]));
				map.setZoom(parseInt(18));
					$('html, body').animate({
						scrollTop: $('.map').offset().top
					}, 600);
					return false;
				});
			} else {
				$('dd a').click(function(){
					return false;
				});
			}
		});
	});

})(jQuery);