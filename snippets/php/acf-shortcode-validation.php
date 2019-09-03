<?php

/**
 * Validates that the shortcode name uses letters and underscores only.
 *
 * @param string $valid Validity message.
 * @param string $value The shortcode string being checked for validity.
 *
 * @return string
 */
function validate_names( $valid, $value ) {

	// Init empty array to be used to hold the keys.
	$keys = array();

	// Loop through the values and push the keys to the array.
	foreach ( $value as $acfGroup ) {
		array_push( $keys, array_keys( $acfGroup ) );
	}

	// Loop through the values based on the amount of keys (field groups).
	for ($i = 0; $i < sizeof( $keys ); $i++ ) {
		// We know that the first cell always holds the shortcode value, no point checking anything but.
		preg_match( '/^[a-zA-Z_]*$/', $value[$i][$keys[$i][0]], $matches );
		if ( sizeof( $matches ) === 0 ) {
			$valid = 'Invalid shortcode name - please use letters and underscores only (no digits or spaces)';
		}
	}
	return $valid;
}