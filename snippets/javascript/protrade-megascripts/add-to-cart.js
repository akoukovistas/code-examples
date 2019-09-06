/**
 * Form submit progress module.
 * @module @form/submitprogress
 * @callback requestCallback
 * todo: Move this into the plugin and make an admin screen to set the various parameters, such as the DOM elements.
 */

var OrderPadHandler = (function() {

	var form   = null,
		rows   = null,
		button = null;

	var pluginDirectory = hsl_php_object.plugin_directory;

	// Create once-only version of submitForm().
	var submitOnce = once(submitForm);

	/**
	 * Initialise the script.
	 */
	function init() {

		form = document.querySelectorAll(hsl_php_object.dom_identifier)[0];
		if (form) {
			rows   = form.getElementsByTagName('tr');
			rows   = Array.prototype.slice.call(rows, 0); // Convert collection to array.
			button = form.getElementsByTagName('button')[0];
			button.addEventListener('click', function(event) {
				// Only allow click once.
				submitOnce(filterRows(rows));
			});
		}
	}

	/**
	 * Operations to perform on submission of the form.
	 *
	 * @param {array.<HTMLElement>} rows An array of DOM elements.
	 */
	function submitForm(rows) {

		if (rows.length === 0) {
			return;
		}

		button.disabled = true;

		// Set waiting status for all populated fields.
		rows.forEach(function(row) {
			setStatus('waiting', row.getElementsByTagName('input')[0]);
		});

		// Recursive call to do stuff with each row.
		processFirstRow(rows);

	}

	/**
	 * Perform operation on first row of array of rows.
	 *
	 * @param {array.<HTMLElement>} rows
	 */
	function processFirstRow(rows) {

		if (rows.length === 0) {
			done();
			return;
		}

		var row    = rows.shift(),
			input  = row.getElementsByTagName('input')[0],
			prodID = parseInt(row.dataset.product),
			varID  = parseInt(row.dataset.variation),
			qty    = parseInt(input.value);

		callAjax(prodID, varID, qty, rows, input, ajaxSuccess, ajaxFail);
	}

	/**
	 * Use various params to perform the actual AJAX call and update the form.
	 *
	 * @param {int} prodID Product ID.
	 * @param {int} varID Product variation ID.
	 * @param {int} qty Quantity.
	 * @param {array} rows Array of table rows from the HTML form.
	 * @param {HTMLElement} element The form element being checked.
	 * @param {requestCallback} callbackSuccess Callback function for success.
	 * @param {requestCallback} callbackFailure Callback function for failure.
	 */
	function callAjax(prodID, varID, qty, rows, element, callbackSuccess, callbackFailure) {

		$.ajax({
			type    : 'post',
			dataType: 'json',
			url     : hsl_php_object.ajax_url,
			data    : {
				action      : 'hsl_add_item_to_cart', // priv_hook in php.
				security    : hsl_php_object.ajax_nonce,
				product_id  : prodID,
				variation_id: varID,
				quantity    : qty,
			},
			success : function(response) {
				callbackSuccess(rows, element, qty);
			},
			error   : function(XMLHttpRequest, textStatus, errorThrown) {
				// You messed up.
				callbackFailure(prodID);
			},
		});
	}

	/**
	 * On successful AJAX call, update the status of the field and call function to process remaining rows.
	 *
	 * @param rows
	 * @param {HTMLElement} element HTML form element.
	 * @param {int} qty Number of items added.
	 */
	function ajaxSuccess(rows, element, qty) {
		updateCartCount(qty);
		setStatus('success', element);
		processFirstRow(rows);
	}

	/**
	 * Trigger some error logging and/or actions to take on AJAX failure.
	 *
	 * @param {int} prodID
	 */
	function ajaxFail(prodID) {
		console.error('Failed to add product to cart: ' + prodID);
	}

	/**
	 * Set status of the form.
	 *
	 * @param {string} status Current status of the AJAX call.
	 * @param {HTMLElement} element DOM element to be styled.
	 */
	function setStatus(status, element) {

		switch (status) {
			case 'waiting':
				element.style.backgroundImage    = 'url("' + pluginDirectory + 'assets/img/spinner-1s-45px.gif")';
				element.style.backgroundPosition = hsl_php_object.spinner_position;
				element.style.backgroundSize     = hsl_php_object.spinner_size;
				element.style.backgroundRepeat   = 'no-repeat';

				break;
			case 'success':
				element.style.background = hsl_php_object.success_bg;
				break;
			default:
				// Nothing to see here. Move along.
				console.error('Unrecognised status');
		}
	}

	/**
	 * Manually update the cart count icon.
	 *
	 * @param {int} quantity Number of items added to cart.
	 */
	function updateCartCount(quantity) {
		var cartCountElement       = document.querySelectorAll('span.cart-count')[0];
		var currentQuantity        = cartCountElement.innerText.replace(/[^\d]/g, '');
		cartCountElement.innerText = '(' + (parseInt(currentQuantity) + quantity) + ')';
	}

	/**
	 * When we have processed all rows, automatically go to basket.
	 */
	function done() {
		window.location = '/' + hsl_php_object.basket_slug;
	}

	/**
	 * Filter out any rows that don't have a quantity in the input field.
	 *
	 * @param {array.<HTMLElement>} rows
	 * @returns array
	 */
	function filterRows(rows) {
		return rows.filter(function(row) {
			var input = row.getElementsByTagName('input')[0];
			return input && input.value > 0;
		});
	}

	/**
	 * Higher-order function to ensure we only respond to initial call.
	 *
	 * @param {function} fn Function to be fired.
	 * @returns {function}
	 */
	function once(fn) {
		var done = false;
		return function(val) {
			if (!done) {
				fn(val);
			}
		};
	}

	// Make functions publicly accessible.
	return {
		init: init,
	};

})();

/**
 * jQuery required for the ajax bit.
 */
jQuery(document).ready(function($) {
	OrderPadHandler.init();
});