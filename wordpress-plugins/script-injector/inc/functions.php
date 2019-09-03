<?php
/**
 *  Check if our post would be one to have post meta for schema.
 *
 * @param $post WP_Post object
 *
 * @return bool
 */
function sin_would_have_meta( $post ) {

	// Get the public post types.
	$args = array(
		'public'   => true
	);
	$post_types = get_post_types($args);

	return is_singular( $post_types );
}
