<?php

// Include the other required files.
include_once( "activation-handler.php" );

// The two activation routes.
define( 'AVA_ACTIVATION', 'api/avaactivations/activate' );
define( 'MYA_ACTIVATION', 'api/myaactivations/activate' );

/**
 * Add rewrites for software activation endpoints.
 */
function raddy_rewrite_software() {
	add_rewrite_rule( '^' . AVA_ACTIVATION . '$', 'index.php?software_activation=1', 'top' );
	add_rewrite_rule( '^' . MYA_ACTIVATION . '$', 'index.php?software_activation=1', 'top' );
}

add_action( 'init', 'raddy_rewrite_software' );

/**
 * Register software activation query var.
 *
 * @param array $query_vars List of query vars.
 *
 * @return array
 */
function raddy_rewrite_software_query_var( $query_vars ) {
	$query_vars[] = 'software_activation';

	return $query_vars;
}

add_filter( 'query_vars', 'raddy_rewrite_software_query_var' );

/**
 * Handle software activation.
 */
function raddy_software_activation() {
	if ( get_query_var( 'software_activation' ) ) {
		raddy_process_activation();
	}
}

add_action( 'wp', 'raddy_software_activation' );

/**
 * Processes the data that's being sent and calls the relevant functions.
 */
function raddy_process_activation() {

	$data = raddy_get_software_activation_data();

	// All the fields in the software are required, just checking for one of them.
	$user_data = raddy_assign_user_data( $data );

	if ( ! empty( $user_data['email'] ) ) {
		$user_activated = raddy_process_user( $user_data );

		if ( is_a( $user_activated, 'WP_User' ) ) {
			raddy_register_activation( $user_activated, $user_data );
		}
	}
}

/**
 * Gets raw JSON POST data as object.
 *
 * @return stdClass
 */
function raddy_get_software_activation_data() {
	$data = file_get_contents( "php://input" );
	$data = stripslashes( $data );
	$data = json_decode( $data );

	return $data;
}

/**
 * This assigns all the data from the request into a usable array.
 *
 * @param array $data
 *
 * @return array The data from the activation request.
 */
function raddy_assign_user_data( stdClass $data ) {

	// Dates come in as UNIX timestamps, let's fix that. Datetime also requires exception handling.
	try {

		$current_date = raddy_convert_date($data->Date);
		$support_from = raddy_convert_date($data->SupportFromDate);
		$support_to   = raddy_convert_date($data->SupportToDate);
	}

	// Catch exception.
	catch( Exception $e ) {

		error_log( $e . "Dates could not be logged succesfully" );
	}

	$user_data = array(
		"title"             => sanitize_text_field( $data->UserTitle ) ?? "", // String max:250.
		"first_name"        => sanitize_text_field( $data->FirstName ) ?? "", // String max:250.
		"last_name"         => sanitize_text_field( $data->LastName ) ?? "", // String max:250.
		"organisation"      => sanitize_text_field( $data->Organisation ) ?? "", // String max:250.
		"department"        => sanitize_text_field( $data->Department ) ?? "", // String max:250.
		"position"          => sanitize_text_field( $data->Position ) ?? "", // String max:250.
		"address1"          => sanitize_text_field( $data->Address1 ) ?? "", // String max:250.
		"address2"          => sanitize_text_field( $data->Address2 ) ?? "", // String max:250.
		"city"              => sanitize_text_field( $data->TownCity ) ?? "", // String max:250.
		"county"            => sanitize_text_field( $data->CountyState ) ?? "", // String max:250.
		"postcode"          => sanitize_text_field( $data->PostZip ) ?? "", // String max:250.
		"country"           => sanitize_text_field( $data->Country ) ?? "", // String - the value from a select.
		"telephone"         => sanitize_text_field( $data->Telephone ) ?? "", // String max:250.
		"email"             => sanitize_text_field( $data->Email ) ?? "", // String max:250.
		"field_of_work"     => sanitize_text_field( $data->Work ) ?? "", // String - the value from a select.
		"organisation_type" => sanitize_text_field( $data->TypeOrganisation ) ?? "", // String - the value from a select.
		"date"              => $current_date->format('Y-m-d-H-i-s') ?? "", // DateTime.
		"activity"          => sanitize_text_field( $data->Activity ) ?? "", // String - "License Purchase" or "License Return".
		"product"           => sanitize_text_field( $data->Product ) ?? "", // String - "AVA" or "Mya 4".
		"additional_info"   => sanitize_text_field( $data->AdditionalInformation ) ?? "", // String - "Licence application succeeded" or "Licence Return Succeeded".
		"software_level"    => sanitize_text_field( $data->SoftwareLevel ) ?? "", // String - "Level 1", "Level 2", "Level 3", "Level 4", or "N/A".
		"serial_no"         => $data->SerialNumber ?? "", // uint, the licence serial defined by Wibu.
		"ticket_no"         => sanitize_text_field( $data->TicketNumber ) ?? "", // string of the form "ABCD-EFGH-IJKL-MNOP".
		"support_from"      => $support_from->format('Y-m-d-H-i-s') ?? "", // DateTime.
		"support_to"        => $support_to->format('Y-m-d-H-i-s') ?? "", // DateTime.
	);

	return $user_data;
}

/**
 * Check if a user exists on the site, if not create one and at the end return which user the activation is for.
 *
 * @param $user_data array The data from the API.
 *
 * @return mixed Either a WP_USER object or WP_ERROR
 */
function raddy_process_user( $user_data ) {

	// Check if a user exists, otherwise create one.
	if ( email_exists( $user_data['email'] ) ) {
		$current_user = get_user_by( 'email', $user_data['email'] );
	} else {
		// Create the new user.
		$email        = sanitize_email( $user_data['email'] );
		$new_user     = register_new_user( $email, $email );
		$current_user = get_user_by( "id", $new_user );

		// Assign all the required values against a user.
		$current_user->set_role( 'customer' );
		update_user_meta( $current_user->ID, "first_name", $user_data['first_name'] );
		update_user_meta( $current_user->ID, "last_name", $user_data['last_name'] );
		update_user_meta( $current_user->ID, "software_title", $user_data['title'] );
		update_user_meta( $current_user->ID, "software_department", $user_data['department'] );
		update_user_meta( $current_user->ID, "software_field_of_work", $user_data['field_of_work'] );
		update_user_meta( $current_user->ID, "software_organisation_type", $user_data['organisation_type'] );
		update_user_meta( $current_user->ID, "software_position", $user_data['position'] );
		update_user_meta( $current_user->ID, "software_country", $user_data['country'] );
		update_user_meta( $current_user->ID, "billing_first_name", $user_data['first_name'] );
		update_user_meta( $current_user->ID, "billing_last_name", $user_data['last_name'] );
		update_user_meta( $current_user->ID, "billing_address_1", $user_data['address1'] );
		update_user_meta( $current_user->ID, "billing_address_2", $user_data['address2'] );
		update_user_meta( $current_user->ID, "billing_city", $user_data['city'] );
		update_user_meta( $current_user->ID, "billing_state", $user_data['county'] );
		update_user_meta( $current_user->ID, "billing_phone", $user_data['telephone'] );
		update_user_meta( $current_user->ID, "billing_company", $user_data['organisation'] );
		update_user_meta( $current_user->ID, "billing_postcode", $user_data['postcode'] );
	}

	// Set the meta key according to the software activated.
	switch ( $user_data['product'] ) {

		case "AVA":
			$raddy_meta_key = "software_ava_status";
			break;
		case "Mya 4":
			$raddy_meta_key = "software_mya_status";
			break;
		default:
			$raddy_meta_key = null;
			break;
	}

	// Only assign a software if the meta key is not null.
	if ( $raddy_meta_key ) {
		update_user_meta( $current_user->ID, $raddy_meta_key, "approved" );
	}

	return $current_user;
}

/**
 * Dates from the activations come as UNIX timestamps WITH MICROSECONDS surrounded by a Date().
 * This strips the useless part out and converts the dates into actual DateTime.
 *
 * @param $date_string
 *
 * @return DateTime
 * @throws Exception
 */
function raddy_convert_date( $date_string ) {

	// This will strip the Date( part as well as the closing ). Also remove the last 3 digits as those are the ms.
	$actual_date = intval( substr( rtrim( str_replace( "Date(", "", $date_string ), ')' ), 0 , -3 ) );

	// Make a new DateTime object and set the timestamp.
	$actual_datetime = new DateTime();
	$actual_datetime->setTimestamp( $actual_date );

	return $actual_datetime;
}
