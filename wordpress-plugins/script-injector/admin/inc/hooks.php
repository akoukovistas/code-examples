<?php

/**
 * This registers the settings for the plugin.
 */
function sin_register_settings() {
	register_setting( 'script-injector-settings', 'script-injector-head-schema' );
	register_setting( 'script-injector-settings', 'script-injector-head-tracking' );
	register_setting( 'script-injector-settings', 'script-injector-body-scripts' );
}


add_action( 'admin_init', 'sin_register_settings' );

/**
 * This registers the menu for the plugin.
 */
function sin_register_menu() {
	add_options_page( 'Script Injector Settings', 'Script Injector', 'manage_sin_options', 'script_injector', function () {
		if ( ! current_user_can( 'manage_sin_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		include( SIN_PLUGIN_DIR . '/admin/templates/settings.php' );
	} );
}

add_action( 'admin_menu', 'sin_register_menu' );

/**
 * This is sanitizing the text areas we are using in the wp-admin area.
 *
 * @param $dirty_textarea
 *
 * @return string
 */
function sin_sanitize_textarea( $dirty_textarea ) {
	$clean_string = sanitize_textarea_field($dirty_textarea);
	return $clean_string;
}

add_action( 'pre_update_option_script-injector-head-schema', 'sin_sanitize_textarea' );
add_action( 'pre_update_option_script-injector-head-tracking', 'sin_sanitize_textarea' );

/**
 * This is used to add custom capabilities for the plugin to users so SEO people can manage it.
 */
function sin_custom_caps() {

	// Get global page now.
	global $pagenow;

	$roles = Array();
	// Add all relevant roles to an array.
	array_push($roles, get_role('administrator' ) );

	if ( get_role( 'wpseo_manager' ) ) {
		array_push( $roles, get_role( 'wpseo_manager' ) );
	}
	if ( get_role( 'wpseo_editor' ) ) {
		array_push($roles, get_role( 'wpseo_editor' ) );
	}

	foreach ( $roles as $role ) {

		// Add a new capability. Try so it doesn't immediately die if the role passed doesn't exist.
		try {
			$role->add_cap('manage_sin_options', true);

			// If we are on the options page ready to save.
			if( $role->name === 'wpseo_editor' || $role->name === 'wpseo_manager'  ) {
				// If the users role is for SEO staff allow the manage options capabilities.
				if ( $pagenow === 'options-general.php' || $pagenow === 'options.php' ) {
					// Allow permission to save the page.
					$role->add_cap( 'manage_options', true );
				} else {
					// Restrict permissions otherwise.
					$role->add_cap( 'manage_options', false );
				}
			}
			
		} catch ( exception $e ) {
			file_put_contents( '.failed-activations', $e);
		}
	}
}

add_action('init', 'sin_custom_caps', 11);

/**
 * This adds metaboxes to custom post types so we can have page-specific schema.
 */
function sin_add_metaboxes() {

	// Get the public post types.
	$args = array(
		'public'   => true
	);
	$post_types = get_post_types($args);

	foreach ( $post_types as $post_type ) {
		add_meta_box(
			'sin_schema_box',
			'Script Injector Schema Box',
			'sin_meta_box_callback',
			$post_type
		);
	}
}

add_action('admin_init', 'sin_add_metaboxes');



/**
 * This is the callback that saves the custom meta box values.
 *
 * @param $post WP_Post
 */
function sin_save_meta( $post_id ) {

	if (array_key_exists('script-injector-post-schema', $_POST)) {
		$clean_text = sanitize_textarea_field( $_POST['script-injector-post-schema'] );
		update_post_meta(
			$post_id,
			'sin_post_schema',
			$clean_text
		);
	}
}

add_action('save_post', 'sin_save_meta');