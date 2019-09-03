<?php

/**
 * This function is injecting stuff into the head.
 */
function script_injector_head_injection() {

	$schema = get_option( 'script-injector-head-schema' );
	$tracking = get_option( 'script-injector-head-tracking' );

	global $post;

	// Let that good schema out in the head.
    echo "<script type='application/ld+json'>\r\n" . $schema . "\r\n";

	// Make sure we're displaying the correct schema.
	if ( sin_would_have_meta( $post ) ) {
		echo get_post_meta($post->ID,'sin_post_schema', true );
	}

	echo "</script>\r\n";
	echo "<script>\r\n" . $tracking . "</script>\r\n";
}
// This actually adds it to the head.
add_action( "wp_head", "script_injector_head_injection" );

/**
 * This function is injecting stuff into the body.
 */
function script_injector_body_injection() {

	$bodyScripts = get_option( 'script-injector-body-scripts' );

	echo $bodyScripts;
}

// Check if the theme has an after body hook and use that, otherwise default to footer injection.
if ( has_action( 'foundationPress_after_body' ) ) {
	add_action( 'foundationPress_after_body', 'script_injector_body_injection' );
} else {
	add_action( 'wp_footer', 'script_injector_body_injection' );
}
