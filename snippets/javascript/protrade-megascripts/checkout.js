/**
 * Custom Woocommerce checkout JS
 *
 */


$(document).ready(function() {

	/*
	SHOW/HIDE COLLECTION LOCATIONS ON CHECKOUT ORDER REVIEW
	*/

	// on load...
	$(window).on('load', function() {

		// first first selected checkbox
		var input = $('.woocommerce-checkout-review-order-table input.shipping_method[checked]').first();

		// show or hide the collection locations based on this input
		dipt_show_hide_collection_locations(input);

	});

	// on shipping method change...
	$(document).on('change', '.woocommerce-checkout-review-order-table input.shipping_method', function() {

		// show or hide the collection locations based on this input
		dipt_show_hide_collection_locations(this);

	});


	// show or hide the collection locations if 'local_pickup' has been selected
	function dipt_show_hide_collection_locations( input ) {

		// get shipping method value
		var shipping_method = $(input).val();

		// is it table rate?
		if ( shipping_method && shipping_method.substr(0, 12) == 'local_pickup' ) {

			// add class to Woo wrapper to show available locations
			$('.woocommerce').addClass('show-collection-select-location');

		} else {

			// remove class from Woo wrapper to hide available locations
			$('.woocommerce').removeClass('show-collection-select-location');

			// remove selection
			$('.dipt-collection-select-location input[type="checkbox"]').prop('checked', false);

		}

	}

});

