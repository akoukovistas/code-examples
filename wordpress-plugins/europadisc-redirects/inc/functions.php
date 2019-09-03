<?php

/**
 * We're getting a URL, splitting into usable parts to get the SKU and then getting the permalink of the matching post and redirecting to it.
 *
 * @param $request_location string This is the URL that a user tried to access.
 */
function euredir_product_redirect( string $request_location ) {

	// Explode the URL into garbage and the product slug.
	$product_sku = explode('/classical/', $request_location);
	// Get the SKU out of the remaining product slug - It's always going to be [1] as we're splitting a URL into 2 parts.
	$product_sku  = substr( $product_sku[1], 0, strpos( $product_sku[1] , "/" ) );

	// This will be 0 if no products have this SKU
	$product_id =  wc_get_product_id_by_sku( $product_sku );

	// Product ID is always going to be an int and we want it not to be 0.
	if( $product_id > 0 ) {

		// Get the permalink and redirect.
		$actual_product = get_permalink( $product_id );
		wp_redirect( $actual_product, 301 );
        exit;
	}

}

/**
 *  We're getting a URL, grabbing and transforming the label name, checking if such a term exists and if so redirect to it. Otherwise die. We're also only doing this for urls from the old site to avoid endless loops.
 *
 * @param $request_location string This is the URL that a user tried to access.
 */
function euredir_label_redirect( string $request_location ) {

	// Check if .htm exists, otherwise it's not an old site URL
	if( euredir_ends_with( $request_location, '.htm' ) === true ) {

		// Let's get the good part first.
		$label_name = explode( 'label/', $request_location );

		// Strip the .htm .
		$label_name = substr( $label_name[1], 0, strpos( $label_name[1], '.htm' ) );

		// Strip anything not alphanumerical.
		$label_name = trim( strtolower( preg_replace( "/[^a-zA-Z0-9]+/",  " ",  $label_name ) ) );

		// If term exists then redirect to it, otherwise 404 like a good script.
		if ( term_exists( $label_name, 'label' ) ) {

			$label_link = get_term_link( $label_name, 'label' );
			wp_redirect( $label_link, 301 );
            exit;
		} else {
			// Since we're here, take them to the archive of labels.
			wp_redirect( site_url() . "/label", 301 );
            exit;
		}
	}

}

/**
 * We're grabbing the ID from the incoming composer URL and matching it against the composer ID meta field to find which page we should redirect to.
 *
 * @param $request_location string This is the URL that a user tried to access.
 */
function euredir_composer_redirect ( string $request_location ) {

    // The format of this is europadisc/search/composers/ID/Composer_Name.htm. We explode on composer and get the second part.
    // This double explode will return only the ID of the composer as we're getting anything before the first / which would be after the ID.
    $old_composer_id = explode ('/', explode ( 'composers/', $request_location )[1] )[0];

    $composer_id = euredir_get_composer_by_old_id( $old_composer_id );

    // Redirect to the archive for the composer.
    $composer_link = get_term_link( (int)$composer_id, 'composer' );

    // Only try redirecting if not an error.
    if ( ! is_wp_error( $composer_link ) ) {
        wp_redirect( $composer_link , 301 );
        exit;
    }
}

/**
 * This will just redirect to the contact form. Nothing fancy.
 */
function euredir_generic_contact_redirect() {
	// This just redirects to the contact page.
	wp_redirect( get_post_permalink( 140638 ), 301 );
    exit;
}