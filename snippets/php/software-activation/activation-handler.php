<?php

// Define the table name.
define( 'ACTIVATIONS_TABLE', 'raddy_software_activations' );

/**
 * we're going to use a separate table to store and process activations.
 */
function raddy_register_db_table() {

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name      = $wpdb->base_prefix . ACTIVATIONS_TABLE;

	$sql = "CREATE TABLE $table_name (
		activation_id INT(100) AUTO_INCREMENT,
		user_id INT(11) NOT NULL,
		user_title VARCHAR(250),
		first_name  VARCHAR(250),
		last_name  VARCHAR(250),
		organisation  VARCHAR(250),
		department  VARCHAR(250),
		user_position  VARCHAR(250),
		address_1  VARCHAR(250),
		address_2  VARCHAR(250),
		town_city  VARCHAR(250),
		county_state  VARCHAR(250),
		post_zip  VARCHAR(250),
		country  VARCHAR(125),
		telephone  VARCHAR(250),
		email  VARCHAR(250),
		field_of_work  VARCHAR(200),
		type_organisation  VARCHAR(200),
		activation_date  DATETIME,
		activity  VARCHAR(200),
		product  VARCHAR(200),
		additional_information  VARCHAR(200),
		software_level  VARCHAR(200),
		serial_number  VARCHAR(200),
		ticket_number  VARCHAR(200),
		support_from_date  DATETIME,
		support_to_date  DATETIME,
		PRIMARY KEY  (activation_id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

add_action( 'admin_init', 'raddy_register_db_table' );

/**
 *  This is where we save the activation data in the database.
 *
 * @param $user WP_USER The user object that has done the activation.
 * @param $user_data array The data from the activation request.
 */
function raddy_register_activation( WP_User $user, array $user_data ) {
	global $wpdb;

	$table_name = $wpdb->base_prefix . ACTIVATIONS_TABLE;

	$wpdb->insert(
		$table_name,
		[
			'user_id'                => $user->ID,
			'user_title'             => $user_data["title"],
			'first_name'             => $user_data["first_name"],
			'last_name'              => $user_data["last_name"],
			'organisation'           => $user_data["organisation"],
			'department'             => $user_data["department"],
			'user_position'          => $user_data["position"],
			'address_1'              => $user_data["address1"],
			'address_2'              => $user_data["address2"],
			'town_city'              => $user_data["city"],
			'county_state'           => $user_data["county"],
			'post_zip'               => $user_data["postcode"],
			'country'                => $user_data["country"],
			'telephone'              => $user_data["telephone"],
			'email'                  => $user_data["email"],
			'field_of_work'          => $user_data["field_of_work"],
			'type_organisation'      => $user_data["organisation_type"],
			'activation_date'        => $user_data["date"],
			'activity'               => $user_data["activity"],
			'product'                => $user_data["product"],
			'additional_information' => $user_data["additional_info"],
			'software_level'         => $user_data["software_level"],
			'serial_number'          => $user_data["serial_no"],
			'ticket_number'          => $user_data["ticket_no"],
			'support_from_date'      => $user_data["support_from"],
			'support_to_date'        => $user_data["support_to"],
		]
	);

	$activity_type = hualb_get_activity_type_from_product( $user_data['product'] );

	$data = array_merge( hualb_get_registered_user_data( $user->ID ) );

	hualb_log( $activity_type, $data );
}

