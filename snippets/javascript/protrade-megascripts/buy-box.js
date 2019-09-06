/**
 * JavaScript functions for the 'Buy Box' element
 *
 */


$( document ).ready( function() {


	/*
	BUY BOX
	*/

	// cart ajax url
	function dipt_cart_ajax_url() {

		// get url
		return $( '.buy-box' ).first().attr( 'data-ajax-url' );

	}


	// select a variation
	$( document ).on( 'change', '.buy-box-select', function() {

		// buy box
		var buy_box = $( this ).closest( '.buy-box' );

		// add loading class
		buy_box.addClass( 'buy-box--loading' );

		// get value (variation id)
		var variation_id = $( this ).val();

		// product id
		var product_id = buy_box.attr( 'data-product-id' );

		// change all selects to the same value (for switching between VAT inc/exc)
		buy_box.find( '.buy-box-select' ).val( variation_id );

		// ajax action
		ajax_action = 'buy-box-add';

		// make ajax url
		var ajax_url = dipt_cart_ajax_url();
		ajax_url += "&action=" + ajax_action;
		ajax_url += "&product=" + product_id;
		ajax_url += "&variation=" + variation_id;

		// ajax post
		$.ajax( {
				type: 'GET',
				url: ajax_url,
			} )
			// run on ajax response
			.complete( function( response ) {

				// have response text?
				if ( response[ 'responseText' ] ) {

					// remove loading class
					buy_box.removeClass( 'buy-box--loading' );

					// decode response
					var response = JSON.parse( response[ 'responseText' ] );

					// have response?
					if ( typeof response[ 'buy_box_add' ] !== 'undefined' ) {

						// place html in container
						buy_box.find( '.buy-box-add-container' ).html( response[ 'buy_box_add' ] );

						// remove empty option from select
						buy_box.find( '.buy-box-select' ).find( 'option[value=""]' ).remove();

					}

					// update url
					var product_link = buy_box.attr( 'data-product-link' );
					var variation_link = product_link + "?variation=" + variation_id;
					window.history.pushState( null, null, variation_link );

				}

			} );

	} );




	/*
	'ADD TO FAVOURITES' BUTTON
	*/


	// button click
	$( document ).on( 'click', '.buy-box-favs-button', function() {

		// find favs interface
		var favs = $( this ).closest( '.buy-box-favs' );

		// add loading class
		$( favs ).addClass( 'buy-box-favs--loading' );

		// get button data attributes
		var ajax_url = $( this ).attr( 'data-ajax-endpoint' );
		var action = $( this ).attr( 'data-ajax-action' );

		// make ajax post
		$.ajax( {
				type: 'GET',
				url: ajax_url,
			} )
			// run on ajax response
			.complete( function( response ) {

				// have response text?
				if ( response[ 'responseText' ] ) {

					// decode response
					var response = JSON.parse( response[ 'responseText' ] );

					// have positive response? (will be indexed by action)
					if ( response[ action ] ) {

						// take action on 'favs' interface
						switch ( action ) {

							case 'add':

								// add class
								$( favs ).addClass( 'buy-box-favs--is-favourite' );

								break;

							case 'remove':

								// remove class
								$( favs ).removeClass( 'buy-box-favs--is-favourite' );

								break;

						}

					}

					// remove loading
					$( favs ).removeClass( 'buy-box-favs--loading' );

				}

			} );

	} );




	/*
	'ADD TO CART' (ATC) BUTTON
	*/


	// action click
	$( document ).on( 'click', '[data-atc-action]', function() {

		// action
		var action = $( this ).attr( 'data-atc-action' );

		// isolate 'add to cart' block
		var atc = $( this ).closest( '.atc' );

		// update
		dipt_atc_update( atc, action );

	} );


	// quantity input changed
	$( document ).on( 'keyup', '.atc-quantity-input', debounce( function() {

		// action
		var action = 'set';

		// isolate 'add to cart' block
		var atc = $( this ).closest( '.atc' );

		// update
		dipt_atc_update( atc, action );

	}, 750 ) );


	/**
	 * The ajax call used to update an items qty in the cart from the product page.
	 *
	 * @param {object} response The response from adding/editing an item in the cart from the product page.
	 * @param {int}    quantity The qty passed from the change qty text input to the ajax call.
	 * @param {object} atc      The element object selector.
	 */
	function dipt_atc_update( atc, action, quantity ) {

		// If we have an action defined and the buy box has been found in the document.
		if ( atc && action ) {

			// Add the loading class.
			$( atc ).addClass( 'atc--loading' );

			// Hide the cart link.
			$('.atc-results-cart-link').hide();

			// Get the params from the buy box.
			var product_id = $( atc ).attr( 'data-product-id' );
			var variation_id = $( atc ).attr( 'data-variation-id' );
			if ( ! quantity ) {
				quantity = parseInt( $( atc ).find( '.atc-quantity-input' ).val() );
			}

			// Ensure we have a integer for the quanitity.
			if ( isNaN( quantity ) ) {
				quantity = 0;
			}
			if ( quantity > 99999 ) {
				quantity = 99999;
			}

			// Based on the action, either increase or decrease the qty in the cart ("+" & "-" buttons).
			switch ( action ) {

				case 'add':
					quantity = quantity + 1;
					break;

				case 'subtract':
					quantity = quantity - 1;
					break;

				case 'set':
					// use passed quantity.
					break;
			}

			// Quantity can't be less than 0
			if ( quantity < 0 ) {
				// If it is, force to 0.
				quantity = 0;
			}

			// Update quantity (so any changes from + or - etc are reflected to the user).
			$( atc ).find( '.atc-quantity-input' ).val( quantity );

			// Define ajax action.
			ajax_action = "set";

			// ajax post
			$.ajax( {
					type: 'GET',
					url: concatinateUpdateCartQtyEndpoint( ajax_action, product_id, variation_id, quantity),
				} )
				// run on ajax response
				.complete( function( response ) {
					// Process the repsonse.
					processUpdateCartResponse(response, quantity, atc);
				} );

		}

		/**
		 * Concatinates the URL used for the ajax call.
		 *
		 * @param {string} action      The action to be called from the ajax handler (serverside).
		 * @param {string} productID   The product whos qty is being added/adjusted in the cart.
		 * @param {string} variationID The variation ID of the products being added/adjusted in the cart.
		 * @param {string} quantity    The qty of the items being added/adjusted in the cart.
		 *
		 * @returns {string}           The URL called.
		 */
		function concatinateUpdateCartQtyEndpoint( action, productID, variationID, quantity) {

			// Start the url.
			var ajaxUrl = dipt_cart_ajax_url();

			// Add the action.
			ajaxUrl = ajaxUrl + "&action=" + action;

			// Add product id.
			ajaxUrl = ajaxUrl + "&product=" + productID;

			// Add variation ID if set.
			if (typeof variationID !== 'undefined') {
				ajaxUrl = ajaxUrl + "&variation=" + variationID;
			}

			// Add the qty.
			ajaxUrl = ajaxUrl + "&quantity=" + quantity;


			return ajaxUrl;
		}

		/**
		 * Used to process the AJAX response from adding/editing an item in the cart from the product page.
		 *
		 * @param {object} response The response from adding/editing an item in the cart from the product page.
		 * @param {int}    quantity The qty passed from the change qty text input to the ajax call.
		 * @param {object} atc      The element object selector.
		 */
		function processUpdateCartResponse(response, quantity, atc) {
			// have response text?
			if ( response[ 'responseText' ] ) {

				// Resize the message div to auto height.
				$('.atc__results.atc__results--show').css({
					height: 'auto'
				});

				// Force redraw of results panel.
				$('.atc__results.atc__results--show').show();

				// Decode the response for use in code.
				var response = JSON.parse( response[ 'responseText' ] );

				// Check we have a reponse.
				if ( response[ ajax_action ] ) {

					// If we have a successful response.
					processUpdateCartResponseSuccess(response, quantity, atc);
				} else {

					// If we dont have a valid response (ERROR)
					processUpdateCartResponseError(response, atc);
				}

				// Show the cart contents.
				processUpdateCartResponseCartContents(response);

				// Remove loading class.
				$( atc ).removeClass( 'atc--loading' );


			}// End if we have a response.
		}

	}
	/**
	 * Used to process a success reponse from the ajax call.
	 *
	 * @param {object} response The response from adding/editing an item in the cart from the product page.
	 * @param {int}    quantity The qty passed from the change qty text input to the ajax call.
	 * @param {object} atc      The element object selector.
	 */
	function processUpdateCartResponseSuccess(response, quantity, atc) {

		// Based on the qty, add/remove the 'atc--have-quantity' class.
		if ( quantity > 0 ) {
			$( atc ).addClass( 'atc--have-quantity' );
		} else {
			$( atc ).removeClass( 'atc--have-quantity' );
		}

		// Parse the response into the HTML.
		var result = false;

		// Based on the QTY either show contents in basket, else show removed.
		if ( quantity > 0 ) {
			result = '<i class="fa fa-check" aria-hidden="true"></i> ' + quantity + ' in your order';
		} else {
			result = '<i class="fa fa-times" aria-hidden="true"></i> Removed from order';
		}

		// Show the results and add the results class.
		$( atc ).find( '.atc-results-message' ).html( result );
		$( atc ).find( '.atc__results' ).addClass( 'atc__results--show' );


		// If quantity is zero, hide results after short delay.
		if ( quantity == 0 ) {
			setTimeout( function() {
				$( atc ).find( '.atc__results' ).removeClass( 'atc__results--show' );
			}, 1500 );
		}

		// If the qty is greater than 1 show the qty box with buttons
		if (quantity >= 1  ) {
			// Show the edit qty box.
			$( '.atc__add' ).hide();
			$( '.atc__quantity' ).show();
		} else {
			// Show the add to basket.
			$( '.atc__quantity' ).hide();
			$( '.atc__add' ).show();
		}

		// If we have 'buy_box_select' html to parse.
		if ( typeof response[ 'buy_box_select' ] !== 'undefined' ) {

			// Place html in container.
			atc.closest( '.buy-box' ).find( '.buy-box-select-container' ).html( response[ 'buy_box_select' ] );

		}
	}

	/**
	 * Show any error messages which come back from the ajax call.
	 *
	 * @param {object} response The response from adding/editing an item in the cart from the product page.
	 * @param {object} atc      The element object selector.
	 */
	function processUpdateCartResponseError(response, atc){
		// Display the error message if set.
		if (typeof response.errors.message !== 'undefined' ) {
			$( atc ).find( '.atc-results-message' ).html( response.errors.message );
		} else {
			// If no error message defined, just show generic error message.
			$( atc ).find( '.atc-results-message' ).html( 'Something went wrong, please try again' );
		}

		// Reset the qty to the existing cart qty, if defined.
		if (typeof response.errors.old_qty !== 'undefined' ) {
			$( atc ).find( '.atc-quantity-input' ).val( response.errors.old_qty );
		}

		// Show the results panel.
		$( atc ).find( '.atc__results' ).addClass( 'atc__results--show' );
	}

	/**
	 * Updates the cart counts in the header and on buy-box in product page.
	 *
	 * @param {object} response The response from adding/editing an item in the cart from the product page.
	 */
	function processUpdateCartResponseCartContents(response) {
		// Show the cart contents if we have a number back .
		if ( typeof response[ 'cart_contents_count' ] === 'number' ) {

			// Make text.
			if ( response[ 'cart_contents_count' ] > 0 ) {
				var cart_count_text = "(" + response[ 'cart_contents_count' ] + ")";
			} else {
				var cart_count_text = "(0)";
			}

			// Update basket indicator in header.
			$( '.cart-customlocation .cart-count' ).html( cart_count_text );

			// Update basket count in 'Add to cart' interface.
			$( '.atc-results-cart-link .cart-count' ).html( cart_count_text );

			// Update the price show for the item on product page, if item has no discounts applied.
			if( ! response['item_prices']['has_discount'] ){
				$( '.buy-box-add-price.vat--inc span.buy-box-add-price__price' ).html( response['item_prices']['price_inc_vat'] );
				$( '.buy-box-add-price.vat--exc span.buy-box-add-price__price' ).html( response['item_prices']['price_ex_vat'] );
			}

			// Show the div.
			$('.atc-results-cart-link').show();

		}// End show cart contents.
	}


	/*
	STOCK
	*/


	// 'View depot stock' click
	$( document ).on( 'click', '.buy-box-location-stock__button', function() {

		// location stock block
		var $location_stock = $( this ).closest( '.buy-box-location-stock' );

		// toggle class
		$location_stock.toggleClass( 'buy-box-location-stock--open' );

	} );

	// Click on anything but the location stock widget...
	$( document ).on( 'click', function( event ) {

		if ( $( event.target ).closest( '.buy-box-location-stock' ).length === 0 ) {

			// close block
			$( '.buy-box-location-stock' ).removeClass( 'buy-box-location-stock--open' );

		}

	} );

} );
