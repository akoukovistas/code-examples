<?php

/**
 * This will check if something is a product from the old site based on the URL.
 *
 * @param $location string The target URL to check against
 *
 * @return bool|int
 */
function euredir_is_product( string $location ) {
	return strpos( $location, "/classical/" );
}

/**
 * This will check if something is a label from the old site based on the URL.
 *
 * @param $location string The target URL to check against
 *
 * @return bool|int
 */
function euredir_is_label( string $location ) {
	// We are fishing for both /label/ and /sublabel/.
	return strpos( $location, "label/" );
}

/**
 * This will check if something is a more info link from the old site based on the URL.
 *
 * @param $location string The target URL to check against
 *
 * @return bool|int
 */
function euredir_is_more_info ( string $location ) {
	return strpos( $location, "/more_information/" );
}

/**
 * This will check if something is a more info link from the old site based on the URL.
 *
 * @param $location string The target URL to check against
 *
 * @return bool|int
 */
function euredir_is_feedback ( string $location ) {
	return strpos( $location, "/feedback/" );
}

/**
 * This will check if something is a more info link from the old site based on the URL.
 *
 * @param $location string The target URL to check against
 *
 * @return bool|int
 */
function euredir_is_composer ( string $location ) {
    return strpos( $location, "/search/composers/" );
}

/**
 * Stolen from stackexchange this checks if the thing ends in the thing.
 *
 * @param $haystack string the string to search in
 * @param $needle string the string to lookup
 *
 * @return bool
 */
function euredir_ends_with( string $haystack, string $needle ){

	$length = strlen( $needle );

	if ( $length == 0 ) {
		return true;
	}

	return ( substr( $haystack, -$length ) === $needle );
}

/**
 * This performs a search within term meta to find a matching meta_key and then grab the term_id.
 * Essentially get_term_by( meta_key, meta_value) which is not a thing in WP.
 *
 * @param $old_composer_id string
 * @return mixed
 */
function euredir_get_composer_by_old_id( string $old_composer_id ) {

    global $wpdb;

    $composer = $wpdb->get_results(
        <<<SQL
            SELECT  term_id FROM {$wpdb->prefix}termmeta
            WHERE `meta_key` = 'wad_composer_id' AND 
            `meta_value` = {$old_composer_id};
SQL
    );

    // It returns a std. object so we need the term_id of the first result.
    return $composer[0]->term_id;
}