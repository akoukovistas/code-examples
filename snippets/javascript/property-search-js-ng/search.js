jQuery(document).ready(function ($) {

	if (!isMobile()) $('.draw-butt').show();

	// Build object with GET data.
	let searchOptions = Object.assign(ng_search.property_search, getUrlVars(true));

	// The property grid container.
	let $propertyGrid = $('.property-search-grid');

	// Default location (NG Chartered HQ).
	let defaultLocation = {lat: 52.934183, lng: -1.135955};

	// 'Home' coordinates for initial map build.
	let lat = searchOptions.lat || defaultLocation.lat;
	let lng = searchOptions.lng || defaultLocation.lng;

	// Set the initial home location coordinates.
	setHomeLocation(lat, lng);

	// Init map.
	let map = initMap();

	// Init a var for the 'home' location. This is tracked so it can be changed/removed easily.
	let homeMarker;

	// Init geocoder for finding lat/lng based on address string.
	let geocoder = new google.maps.Geocoder();

	// Init bounds object for easily building/clearing bounds.
	let bounds = new google.maps.LatLngBounds();

	// Init the circle for radius (not drawn by default).
	let radiusCircle = createRadiusCircle();

	// Init drawingMode object.
	let drawingMode = initDrawingMode();

	// Init polygon placeholder for drawing mode.
	let polygons = [];

	// Initial assignment of map data to filterable properties.
	initMarkers();

	// Initial assignment of properties to filter variable.
	let processedProperties = ng_search.properties;

	// Populate form elements with URL vars.
	prePopulateFilters();

	// Initial build of the property grid.
	processFilters();

	// Switch to view.
	if (searchOptions.view) {
		switchView(searchOptions.view);
	}

	// Listen for property filter changes.
	$('input#property-search').keypress(onSearchEnter); // Enter key in search input
	$('#searchsubmit').click(processFilters); // Search button
	$('.property-type').change(processFilters); // Type (radio button)
	$('.property-filter').change(processFilters); // Filters (selects)
	$('.property-sort').change(processFilters); // Sort (select)

	// Listen for radius circle drag finish.
	google.maps.event.addListener(radiusCircle, 'dragend', onDragendRadiusCircle);

	// Listen for view switch click.
	$('.view-switch').on('click', onViewSwitchClick);

	// Prevent typical form submission.
	$('form#property-filter-form').submit(e => {
		e.preventDefault();
	});

	window.onpopstate = resetHistoryState;

	// Trigger geolocation.
	let $geolocator = $('.geolocatatron');
	if (navigator.geolocation) {
		$geolocator.on('click', geolocate);
	} else {
		$geolocator.parent().remove();
	}

	// Trigger polygon draw handling.
	$('.draw-butt').on('click', handlePolygonDraw);

	/**
	 * Process search on history change.
	 * @param event
	 */
	function resetHistoryState(event) {
		searchOptions = event.state;
		prePopulateFilters();
		processFilters(false);
	}

	/**
	 * Set lat/lng to stated (or default, if null) location.
	 * @param lat
	 * @param lng
	 */
	function setHomeLocation(lat = null, lng = null) {
		lat = parseFloat(lat) || defaultLocation.lat;
		lng = parseFloat(lng) || defaultLocation.lng;
		searchOptions.lat = lat;
		searchOptions.lng = lng;
	}

	/**
	 * Create radius circle.
	 * @returns {*}
	 */
	function createRadiusCircle() {
		return new google.maps.Circle({
			strokeColor: '#78C12A',
			strokeOpacity: 0.8,
			strokeWeight: 2,
			fillColor: '#78C12A',
			fillOpacity: 0.35,
			draggable: true,
		});
	}

	/**
	 * Handle polygon drawing.
	 * @param e
	 */
	function handlePolygonDraw(e) {

		// Prevent form submission.
		e.preventDefault();

		let $searchInput = $('input[name="property-search"]');
		let $searchButt = $('button#searchsubmit');
		let $searchradius = $('select[name="property-radius"]');

		if (!$(this).hasClass('active')) {
			// Change button to reflect active status.
			$(this).addClass('active').html('Clear drawing');

			// Disable search and radius elements.
			$searchInput.attr('disabled', true).attr('placeholder', $searchInput.data('placeholder-disabled'));
			$searchButt.attr('disabled', true);
			$searchradius.attr('disabled', true);

			// Remove radius circle if it exists.
			radiusCircle.setMap(null);

			// Start drawing!
			startDraw();
		} else {
			// Change button to reflect inactive status.
			$(this).removeClass('active').html('Draw area');

			// Enable search and radius elements.
			$searchInput.attr('disabled', false).attr('placeholder', $searchInput.data('placeholder-enabled'));
			$searchButt.attr('disabled', false);
			$searchradius.attr('disabled', false);

			// Stop drawing!
			endDraw();
		}
	}

	function startDraw() {

		// Set drawing mode and add to map.
		drawingMode.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
		drawingMode.setMap(map);

		// Clear markers to make way for the polygon.
		clearMarkers();

		// Listen for completed polygon.
		google.maps.event.addListener(drawingMode, 'polygoncomplete', function (e) {
			// Prevent further drawing so markers can be clicked.
			drawingMode.setDrawingMode(null);
			// Assign polygon to placeholder for use elsewhere.
			polygons.push(e);
			// Redraw the map.
			processFilters();
		});
	}

	function endDraw() {
		// Remove listeners so polygon can be drawn again!
		google.maps.event.clearListeners(drawingMode, 'polygoncomplete');

		// Clear drawingmode object from map.
		drawingMode.setDrawingMode(null);
		drawingMode.setMap(null);

		// Redraw the map.
		processFilters();
	}

	/**
	 * Init the DrawingManager object with some style.
	 * @returns {google.maps.drawing.DrawingManager}
	 */
	function initDrawingMode() {
		return new google.maps.drawing.DrawingManager({
			drawingControl: false,
			polygonOptions: {
				strokeColor: '#78C12A',
				strokeOpacity: 0.8,
				strokeWeight: 2,
				fillColor: '#78C12A',
				fillOpacity: 0.35,
			}
		});
	}

	/**
	 * Attempt to geolocate user.
	 * @param e
	 */
	function geolocate(e) {
		e.preventDefault();
		// Try HTML5 geolocation.
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function (position) {
				lat = position.coords.latitude;
				lng = position.coords.longitude;
				setHomeLocation(position.coords.latitude, position.coords.longitude);
				let pos = {
					lat: parseFloat(lat),
					lng: parseFloat(lng)
				};
				map.setCenter(pos);
				if (radiusCircle.map) radiusCircle.setCenter(pos);
				processFilters();
			}, function () {
				alert('Geolocation failed.');
			});
		}
	}

	/**
	 * Process filters based on circle location.
	 */
	function onDragendRadiusCircle() {
		lat = radiusCircle.center.lat();
		lng = radiusCircle.center.lng();
		setHomeLocation(lat, lng);
		// Clear the search filter.
		$('#property-search').val('');
		updateSearchOptions({'property-search': ''});
		// Finally, re-process filters.
		processFilters();
	}

	/**
	 * Init map.
	 * @returns {*}
	 */
	function initMap() {
		return new google.maps.Map(document.getElementById('mon-map'), {
			center: {lat: lat, lng: lng},
			zoom: 16
		});
	}

	/**
	 * Add markers to properties.
	 */
	function initMarkers() {
		ng_search.properties = ng_search.properties.map(property => {
			// Create the marker.
			property.latlng = new google.maps.LatLng(property.latitude, property.longitude);
			property.marker = new google.maps.Marker({
				position: property.latlng,
				visible: true
			});
			// Create infowindow to attach to marker.
			property.infoWindow = new google.maps.InfoWindow({
				content: property.markup
			});
			// Add handler to open the infowindow on marker click.
			property.marker.addListener('click', function () {
				property.infoWindow.open(map, property.marker);
			});
			return property;
		});
	}

	/**
	 * Add home marker. This marker is invisible but ensures that location remains in bounds when no results found.
	 */
	function setHomeMarker() {
		if (typeof homeMarker !== 'undefined') homeMarker.setMap(null);
		let position = new google.maps.LatLng({lat: parseFloat(lat), lng: parseFloat(lng)});
		homeMarker = new google.maps.Marker({
			map: map,
			position: position,
			visible: false
		});
		homeMarker.setMap(map);
		bounds.extend(position);
	}

	/**
	 * Set the markers and fit bounds to them.
	 */
	function setMarkers() {

		setHomeMarker();

		for (let pp = 0; pp < processedProperties.length; pp++) {
			processedProperties[pp].marker.setMap(map);
			bounds.extend(processedProperties[pp].latlng);
		}

		fitBounds();
	}

	/**
	 * Fit map to appropriate bounds.
	 */
	function fitBounds() {
		if (polygons.length) {
			map.fitBounds(getPolygonBounds(polygons));
		} else if (radiusCircle && radiusCircle.map) {
			map.fitBounds(radiusCircle.getBounds());
		} else {
			map.fitBounds(bounds);
			// Reduce zoom if no properties are found.
			if (processedProperties.length === 0) {
				google.maps.event.addListenerOnce(map, "idle", function () {
					map.setZoom(14);
				});
			}
		}
	}

	/**
	 * Given an array of polygons, return a set of bounds.
	 * @param polygons
	 * @returns {google.maps.LatLngBounds}
	 */
	function getPolygonBounds(polygons) {
		bounds = new google.maps.LatLngBounds();
		for (let i = 0; i < polygons.length; i++) {
			let paths = polygons[i].getPaths();
			paths.forEach(function (path) {
				let ar = path.getArray();
				for (let i = 0, l = ar.length; i < l; i++) {
					bounds.extend(ar[i]);
				}
			});
		}

		return bounds;
	}

	/**
	 * Clear all markers.
	 */
	function clearMarkers() {

		// Clear all the markers.
		for (let p = 0; p < ng_search.properties.length; p++) {
			ng_search.properties[p].marker.setMap(null);
		}

		// Reset bounds.
		bounds = new google.maps.LatLngBounds();
	}

	/**
	 * Get lat/lng of entered address.
	 * @param address
	 * @param callback
	 */
	function updateAddress(address, callback) {

		removeNotification();

		// Enforce UK search
		address = address + ' UK';
		geocoder.geocode({'address': address}, function (results, status) {
			if (status === 'OK') { // All is well!
				map.setCenter(results[0].geometry.location);
				lat = results[0].geometry.location.lat();
				lng = results[0].geometry.location.lng();
				ng_search.properties = setDistances(ng_search.properties, lat, lng);
				processedProperties = setDistances(processedProperties, lat, lng);
			} else { // Something went wrong. :(
				addNotification();
			}

			// Run the callback to proceed with the rest of the process.
			callback();
		});
	}

	/**
	 * Remove the generic notification.
	 */
	function removeNotification() {
		$('.view-controls__results .notification').remove();
	}

	/**
	 * Add a generic notification for missing notification.
	 */
	function addNotification() {
		$('.view-controls__results').append($('<div>', {
			class: 'notification callout alert',
			html: 'Location could not be found - please try searching for an alternative location.'
		}));
	}

	/**
	 * Check for enter key being pressed on focused search input
	 * @param e
	 */
	function onSearchEnter(e) {
		let keycode = e.keyCode || e.which;
		if (keycode == 13) {
			e.preventDefault();
			processFilters();
			return false;
		}
	}

	/**
	 * Add distances to all properties
	 * @returns {*}
	 */
	function setDistances(properties, homeLat, homeLng) {
		return properties.map(property => {
			property.distance = distance(homeLat, homeLng, property.latitude, property.longitude);
			return property;
		});
	}

	/**
	 * Populate filters with values from URL vars
	 */
	function prePopulateFilters() {
		for (let s in searchOptions) {
			let $element = $('[name="' + s + '"]:not(:hidden)');
			if ($element.is('input[type="radio"]')) {
				$element.each((i, e) => $(e).attr('checked', ($(e).val() === searchOptions[s])));
			} else {
				$element.val(searchOptions[s]);
			}
		}
	}

	/**
	 * Runs through all filters and rebuilds the property grid
	 */
	function processFilters(doUpdateUrl = true) {
		ng_search.properties = setDistances(ng_search.properties, lat, lng);
		processedProperties = ng_search.properties;

		// Filters.
		onSubmitSearch(function () {
			onChangePropertyType();
			onChangePropertyFilter();
			onChangePropertySort();

			// Update map markers.
			clearMarkers();
			setMarkers();

			// Rebuild grid and update URL.
			rebuildPropertyGrid(processedProperties);
			if (doUpdateUrl) updateUrl();
		});
	}

	/**
	 * Dynamically update URL variables with selected filters
	 */
	function updateUrl() {
		let searchArray = [];
		for (let s in searchOptions) {
			if (searchOptions[s] !== '') searchArray.push(encodeURIComponent(s) + '=' + encodeURIComponent(searchOptions[s]));
		}
		history.pushState(searchOptions, 'Property Search', '?' + searchArray.join('&'));
	}

	/**
	 * Merge updated options with existing options and update URL/history
	 *
	 * @param updatedOptions
	 */
	function updateSearchOptions(updatedOptions) {
		searchOptions = Object.assign(searchOptions, updatedOptions);
		updateUrl();
	}

	/**
	 * Handles search submission
	 */
	function onSubmitSearch(callback) {

		$('.view-controls__results notification').remove();

		let originalSearchText = $('#property-search').val();

		searchOptions['property-search'] = originalSearchText;

		// Attempt to geocode the location if there is search text.
		if (originalSearchText) {
			updateAddress(originalSearchText, callback);
		} else {
			// Set default home location if circle isn't on the map.
			if (!radiusCircle.map) {
				setHomeLocation();
			}
			callback();
		}
	}

	/**
	 * Handles property type change
	 */
	function onChangePropertyType() {
		let selectedType = $('input[name="property-type"]:checked').val();
		processedProperties = processedProperties.filter(property => property.property_types.indexOf(parseInt(selectedType)) > -1);
		let price_type = 'price_' + ((parseInt($('.property-type:checked').val()) === 19) ? 'freehold' : 'leasehold');
		processedProperties = processedProperties.map(property => {
			property.price = parseInt(property[price_type]);
			let price_string = formatMoney(property.price, 0).toString();
			property.price_string = (property.price > 0) ? '£' + price_string + ' per annum' : 'Price on application';
			return property;
		});
		searchOptions['property-type'] = selectedType;
	}

	/**
	 * Handles filter change
	 */
	function onChangePropertyFilter() {

		// Ensure that existing poly is cleared if dm not set.
		if (!drawingMode.map && polygons.length) {
			polygons.map(polygon => {
				polygon.setMap(null);
				return polygon;
			});
			polygons = [];
		}

		let selectedSector = $('select[name="property-sector"]:not(:hidden)').val();
		let selectedSize = $('select[name="property-size"]:not(:hidden)').val();
		let selectedRadius = $('select[name="property-radius"]:not(:hidden)').val();

		if (selectedSector !== '') {
			processedProperties = processedProperties.filter(property => property.sectors.indexOf(parseInt(selectedSector)) > -1);
		}

		if (selectedSize !== '') {
			processedProperties = processedProperties.filter(property => parseInt(property.size) === parseInt(selectedSize));
		}

		if (drawingMode.map) {
			radiusCircle.setMap(null);
			processedProperties = processedProperties.filter(property => {
				let inPolygon = false;
				for (let i = 0; i < polygons.length; i++) {
					if (google.maps.geometry.poly.containsLocation(property.latlng, polygons[i])) {
						inPolygon = true;
					}
				}
				return inPolygon;
			});
		} else {
			if (selectedRadius !== '') {
				processedProperties = processedProperties.filter(property => property.distance <= parseInt(selectedRadius));
				let homePosition = new google.maps.LatLng({lat: parseFloat(lat), lng: parseFloat(lng)});
				radiusCircle.setRadius(parseInt(selectedRadius * 1000));
				radiusCircle.setCenter(homePosition);
				map.setCenter(homePosition);
				radiusCircle.setMap(map);
			} else {
				radiusCircle.setMap(null);
			}
		}

		updateSearchOptions({
			'property-sector': selectedSector,
			'property-size': selectedSize,
			'property-radius': selectedRadius
		});
	}

	/**
	 * Handles sorting
	 */
	function onChangePropertySort() {

		let selectedSort = $('select[name="property-sort"]').val();

		switch (selectedSort) {
			case 'price-desc':
				processedProperties = processedProperties.sort((a, b) => parseInt(b.price) - parseInt(a.price));
				break;
			case 'price-asc':
				processedProperties = processedProperties.sort((a, b) => parseInt(a.price) - parseInt(b.price));
				break;
			case 'date-desc':
				processedProperties = processedProperties.sort((a, b) => parseInt(b.date_posted) - parseInt(a.date_posted));
				break;
			case 'date-asc':
				processedProperties = processedProperties.sort((a, b) => parseInt(a.date_posted) - parseInt(b.date_posted));
				break;
		}

		updateSearchOptions({'property-sort': selectedSort});
	}

	/**
	 * Empties and rebuilds the grid.
	 * @param props
	 */
	function rebuildPropertyGrid(props) {
		$propertyGrid.empty();
		$('.property-search-results-grid--notification').remove();
		if (typeof props === 'undefined') props = ng_search.properties;
		$('.view-controls__count').text(props.length + ' premises found');
		if (props.length) {
			props.forEach(function (prop) {
				$propertyGrid.append($(prop.markup));
				$('a#property-' + prop.property_id + ' .card__price').html(prop.tenure);
			});
		} else {
			$('.property-search-results-grid').append($('<div>', {
				class: 'property-search-results-grid--notification notification callout alert',
				html: 'No properties found. Please try different filters above.'
			}));
		}

		if ($('input[name="property-type"]:checked').length === 0) {
			$('input[name="property-type"]')[0].prop('checked', true);
		}

		setMatchHeightOnGrid();
	}

	/**
	 * Handle switch view change.
	 * @param e
	 */
	function onViewSwitchClick(e) {

		e.preventDefault();

		if ($(this).hasClass('view-switch-map')) {
			switchView('map');
		}
		if ($(this).hasClass('view-switch-grid')) {
			switchView('grid');
		}
	}

	/**
	 * Switch the view.
	 * @param type
	 */
	function switchView(type) {

		let $switchMap = $('.view-switch-map');
		let $switchGrid = $('.view-switch-grid');

		let $map = $('.property-search-results-map');
		let $grid = $('.property-search-results-grid');

		if (type === 'map') {
			handleShowHide($map, $grid, $switchMap, $switchGrid, function () {
				updateSearchOptions({view: 'map'});
				if (!isMobile()) $('.draw-butt').show();
				$('[name="property-sort"]').hide();
				google.maps.event.trigger(map, 'resize');
				fitBounds();
			});
		}
		if (type === 'grid') {
			handleShowHide($grid, $map, $switchGrid, $switchMap, function () {
				updateSearchOptions({view: 'grid'});
				$('.draw-butt').hide();
				$('[name="property-sort"]').show();
				setMatchHeightOnGrid();
			});
		}
	}

	/**
	 * Swap active/inactive view and button.
	 * @param $showEl
	 * @param $hideEl
	 * @param $enableButt
	 * @param $disableButt
	 * @param showCallback
	 */
	function handleShowHide($showEl, $hideEl, $enableButt, $disableButt, showCallback) {
		let activeClass = 'ng-chartered-button__full-background ng-chartered-button__full-background--green active';
		let inactiveClass = 'ng-chartered-button__outline ng-chartered-button__outline--green';
		$enableButt.addClass(activeClass).removeClass(inactiveClass);
		$disableButt.addClass(inactiveClass).removeClass(activeClass);
		$hideEl.slideUp(400);
		$showEl.slideDown(400, showCallback);
	}
});