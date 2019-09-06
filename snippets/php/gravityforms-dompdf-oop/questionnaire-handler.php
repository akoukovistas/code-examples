<?php

/**
 * Dev notes for future prosperity
 * Organization Checklist = GF 8
 */

declare( strict_types = 1 );

use CraftyAnnie\Pdf;
require_once ( "pdf/PdfEntry.php" );

define( 'EMAIL_FIELD_ID', 22 );
define( 'LAST_HEADING_FIELD_ID', 4 );
define( 'AC_THANK_YOU_PAGE_ID', 968 );

/**
 * Convenience function to build and write a PDF from HTML.
 *
 * @param  string  $html
 *
 * @return Pdf\PdfMaker
 */
function craftyannie_create_checklist_attachment( string $html ): Pdf\PdfMaker {
	$pdf = craftyannie_pdf_handler();
	$pdf->make( $html );
	$pdf_path = "/library/pdf/";
	$pdf_name = 'safeguarding-checklist_' . time() . '.pdf';
	$pdf->set_name( $pdf_name );
	$pdf->save( get_stylesheet_directory() . $pdf_path . $pdf_name );

	return $pdf;
}

/**
 * This will generate and attach a pdf of the resources based on the answers given by a user taking the questionnaire.
 *
 * @param array $notification the Gforms notification object
 * @param array $form
 * @param array $entry
 * @return array
 */
function generate_and_attach_pdf_to_notification( array $notification, array $form, array $entry ): array {

	if ( $notification['name'] == 'Admin Notification' ) {
		craftyannie_generate_pdf_based_based_on_entry_and_form( $entry, $form );
	}

	// Entry[22] is the email field - check if there's an email,if it's an actual email and if it's the user notification - otherwise skip pdf generation.
	if ( $entry[EMAIL_FIELD_ID] && filter_var( $entry[EMAIL_FIELD_ID], FILTER_VALIDATE_EMAIL ) && $notification['name'] == 'User Notification' ) {

		// Get the pdf object from the transient.
		$transient_data = get_transient('ac_pdf_generation_' . $entry['id'] );

		// User details.
		$details['email'] = sanitize_email( $entry[EMAIL_FIELD_ID] );

		// Email details.
		$details['title']     = 'Ann Craft Trust - Safeguarding Checklist';
		$details['subject']   = 'Ann Craft Trust - Your Safeguarding Checklist';

		if ( file_exists( $transient_data['pdf_path'] ) ) {
			$notification['attachments']   = rgar( $notification, 'attachments', array() );
			$notification['attachments'][] = $transient_data['pdf_path'];
		} else {
			GFCommon::log_debug( __METHOD__ . '(): not attaching; file does not exist.' );
		}
	}


	return $notification;
}

// Attach the pdf generation to the form notifications.
add_action( 'gform_notification_8', 'generate_and_attach_pdf_to_notification', 10, 3 );

/**
 * Process the submitted questionnaire and assign values to an array to be used with pdf generation.
 *
 * @param array $entry_data this contains all the Gravity Form field values for the entry
 * @param array $entry_form this is the Gravity Form that's being used
 * @return array
 */
function craftyannie_compile_pdf_entry_data( array $entry_data, array $entry_form ) : array {

	// Loop through the form fields to get the info we need first.
	foreach ( $entry_form['fields'] as $form_field ) {

		//Let's insert some headings in the array too. Only if they are not containing X of X and it is not the final section title.
		if ( $form_field instanceof GF_Field_Section && ctype_alpha( $form_field['label'] ) && $form_field["id"] != LAST_HEADING_FIELD_ID ) {
			$pdf_entry_data[] = [
				"field_id" => $form_field['id'],
				"field_content" => $form_field['label'],
				"field_type" => "title"
				];
		}

		// We only need to worry about radio buttons.
		if ( $form_field instanceof GF_Field_Radio ) {

			// Create an array containing the field_id and the form entry object.
			$pdf_entry_data[] = [
				"field_id" => $form_field['id'],
				"entry_object" => new Pdf\PdfEntry( $form_field['label'], $form_field['description'], $entry_data[$form_field['id']]),
				"field_type" => "answer"
			];
		}
	}

	$pdf_entry_data = craftyannie_match_entry_data_to_acf( $pdf_entry_data, (int)$entry_form['id'] );

	return $pdf_entry_data;
}

/**
 * Match the pdf entry data to any acf fields to pull in the supporting content.
 *
 * @param array $pdf_entry_data
 * @param int $form_id
 * @return array
 */
function craftyannie_match_entry_data_to_acf( array $pdf_entry_data, int $form_id ) : array {

	// Check that ACF is actually a thing and we (probably) have an options page.
	if ( function_exists( "acf_add_options_page" ) ) {

		//Check which form we're actually doing this for.
		switch ( $form_id ) {

			// GF8 = Organisation.
			case 8:
				$organization_supporting_content_field_name = 'ac_safeguarding_organisation_supporting_content';
				$organization_supporting_content_field_value = get_field( $organization_supporting_content_field_name, 'option' );

				foreach ( $pdf_entry_data as $pdf_entry_datum ) {

					if ( $pdf_entry_datum['field_type'] === "answer" ) {
						if ( mb_strtolower( $pdf_entry_datum['entry_object']->get_entry_answer() ) === "no" && have_rows( $organization_supporting_content_field_name, 'option' ) ) {

							foreach ( $organization_supporting_content_field_value as $subfield ) {
								if ( (int) $pdf_entry_datum["field_id"] === (int) $subfield["ac_gravity_form_field_id"] ) {
									// Assign the support content and break out of the loop.
									$pdf_entry_datum['entry_object']->set_entry_support_content( $subfield["ac_supporting_content"] );
									break;
								}
							}

						}
					}
				}
			break;
			// If this happens, notify the developer that the form is actually in fact an unexpected form.
			default:
				// There's no need to be upset.
				error_log( "You have used a form with an illegal ID! The PDF Generator salutes you kindly. Form ID: " . $form_id );
			break;
		}
	}

	// Regardless return whatever the array looks like.
	return $pdf_entry_data;
}

/**
 * Check if it is the thank you page
 *
 * @param string $the_content The post content.
 * @return string
 */
function craftyannie_attach_pdf_to_thank_you_page( $the_content ) {

	global $post;

	// Check if we are on the thank you page.
	if ( $post->ID == AC_THANK_YOU_PAGE_ID ) {
		if ( isset ( $_GET['entry_id'] ) ){
			$transient_data = get_transient('ac_pdf_generation_' . $_GET['entry_id'] );
			$the_content .= "<span class='ac-pdf_download-url'><a href=" . urldecode( $transient_data['pdf_url'] ) . ">Click here to download your copy of the checklist</a></span>";
		}
	}

	return $the_content;
}

add_filter( 'the_content', 'craftyannie_attach_pdf_to_thank_you_page' );

/**
 * This function will generate a pdf based on an entry and a form
 *
 * @param array $entry
 * @param array $form
 * @return Pdf\PdfMaker
 */
function craftyannie_generate_pdf_based_based_on_entry_and_form( array $entry, array $form ): void {

	$details['pdf_entry_data'] = craftyannie_compile_pdf_entry_data( $entry, $form );

	$details['pdf_title'] = 'Your Safeguarding Checklist';

	// Load in the two WSYWIG ACF Fields.
	$details['pdf_intro'] = get_field( "ac_email_intro_area", 'option' );
	$details['pdf_resources'] = get_field( "ac_email_resources_area", 'option' );
	$pdf_image_path = get_stylesheet_directory() . '/assets/images/logo.png';

	// Create the czechlist HTML.
	ob_start();
	include_once get_stylesheet_directory() . '/template-parts/pdf/safeguarding-checklist.php';
	$html = ob_get_clean();

	// Make the wishlist file.
	$pdf = craftyannie_create_checklist_attachment( $html );

	// Set a cookie containing the PDF URL.
	$transient_data = [
		"pdf_path" => $pdf->get_path(),
		"pdf_url" => urlencode( get_stylesheet_directory_uri() . '/library/pdf/' . $pdf->get_name() )
	];

	set_transient( 'ac_pdf_generation_' . $entry['id'], $transient_data );

}
