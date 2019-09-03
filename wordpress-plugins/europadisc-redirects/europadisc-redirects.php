<?php
defined( 'ABSPATH' ) or die();

/**
 * Plugin Name:     Europadisc Redirects
 * Plugin URI:      https://www.hallaminternet.com
 * Description:     This plugin handles all the automated redirects from the old site
 * Author:          Alexander at Hallam
 * Author URI:      https://wwww.hallaminternet.com
 * Text Domain:     europadisc-redirects
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Europadisc_Redirects
 */

// Your code starts here. - Don't tell me what to do, I'm keeping this comment in.

// Define constants :) .
define( 'EUREDIR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'EUREDIR_PLUGIN_NAME', trim( dirname( EUREDIR_PLUGIN_BASENAME ), '/' ) );
define( 'EUREDIR_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
define( 'EUREDIR_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

// Include script paths.
include EUREDIR_PLUGIN_DIR . '/inc/utility-functions.php';
include EUREDIR_PLUGIN_DIR . '/inc/functions.php';

/**
 * This is the main redirect function, it checks if it's a 404, resolves the type of redirect required and then calls the relevant function.
 */
function euredir_check_redirect() {

    $request_location = $_SERVER['REQUEST_URI'];

	// Only do this is the URL is dead or it is a composer search.
	if( is_404() || euredir_is_composer( $request_location ) ) {

		// Is this a product? a label? superman?
		if( euredir_is_product( $request_location ) ) {
			// Product.
			euredir_product_redirect( $request_location );
		} else if ( euredir_is_label( $request_location ) ) {
			// Label.
			euredir_label_redirect( $request_location );
		} else if ( euredir_is_more_info( $request_location ) || euredir_is_feedback( $request_location ) ) {
			// More info.
			euredir_generic_contact_redirect();
		} else if ( euredir_is_composer( $request_location ) ) {
		    // Composer.
            euredir_composer_redirect( $request_location );
        }
	}
}

// We call it on template_redirect to intercept the 404 errors.
add_action('template_redirect', 'euredir_check_redirect');

