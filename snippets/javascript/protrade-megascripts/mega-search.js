/**
 * JavaScript functions for the 'Mega Search' feature
 *
 */


$(document).ready(function() {

	// Click anywhere outside the mega search to close it
	$(document).on('click', 'body',  function(e) {

		var megasearch = $('.mega-search__menu');

		// if the target of the click isn't the container nor a descendant of the container
		if (!megasearch.is(e.target) && megasearch.has(e.target).length === 0) {

			// Remove active class
			$('#mega-search').removeClass('active');

		}

	});


	// When user types into search box
	$(document).on('keyup', '#s, #s-mobile',  debounce( function() {

		// Identify mega search
		var megasearch = $(document).find('#mega-search').first();

		// Search query 
		var query = $(this).val();

		// Minimum length of query to trigger mega search
		var min_length = 4;

		// Test length of query
		if ( query.length < min_length ) {

			// Remove active class
			$(megasearch).removeClass('active');

		} else {

			// Get ajax url
			var ajax_url = $(megasearch).data('ajax-endpoint');
			if ( !ajax_url ) { return false; }

			// Mobile mode?
			var mobile = 0;

			/*
			GET PRODUCTS
			*/

			// Identify product container
			var product_container = $(megasearch).find("#mega-search-product-container");

			// Set ajax action
			var ajax_action = 'get-products';

			// Complete ajax url
			ajax_url += "&action=" + ajax_action;
			ajax_url += "&query=" + query;
			ajax_url += "&mobile=" + mobile;

			// Make ajax get request
			$.ajax({
				type: 		'GET',
				url: 		ajax_url,
			})
			.complete(function(response) {

				// Have response text?
				if (response['responseText']) {

					// Empty product container
					$(product_container).empty();

					// Decode response
					var response = JSON.parse(response['responseText']);

					// Update products count
					$(megasearch).find("#mega-search-total-prodcts").html(response['total_products']);

					// Update 'see all' link
					$(megasearch).find("#mega-search-see-all-prodcts").attr('href', response['all_products_link']);

					// Have cards?
					if ( response['product_cards'].length > 0 ) {

						// Loop cards
						for (var i = 0; i < response['product_cards'].length; i++) {

							// Append card html
							$(product_container).append(response['product_cards'][i]);

						}

						// Add active class
						$(megasearch).addClass('active');

					}

				} else {

					// Remove active class
					$(megasearch).removeClass('active');

				}

			});


			/*
			GET CATEGORIES
			*/

			// Identify cat container
			var cat_container = $(megasearch).find("#mega-search-cat-container");

			// Set ajax action
			var ajax_action = 'get-cats';

			// Complete ajax url
			ajax_url += "&action=" + ajax_action;
			ajax_url += "&query=" + query;
			ajax_url += "&mobile=" + mobile;

			// Make ajax get request
			$.ajax({
				type: 		'GET',
				url: 		ajax_url,
			})
			.complete(function(response) {

				// Have response text?
				if (response['responseText']) {

					// Empty cat container
					$(cat_container).empty();

					// Decode response
					var response = JSON.parse(response['responseText']);

					// Have cats?
					if ( response['cats'].length > 0 ) {

						// Loop cats
						for (var i = 0; i < response['cats'].length; i++) {

							// Isolate category
							var cat = response['cats'][i];

							// Make html string and append to container
							$('<li><a href="' + cat['link'] + '">' + cat['name'] + '</a> (' + cat['count'] + ')</li>').appendTo(cat_container);

						}

					}

				}

			});


		}

	}, 200));

});




/*
UTILITIES
*/


// debounce utility
// https://davidwalsh.name/javascript-debounce-function
function debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
};

