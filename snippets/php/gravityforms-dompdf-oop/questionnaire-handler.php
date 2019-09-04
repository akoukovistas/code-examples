<?php

/**
 * Dev notes for future prosperity
 * Organization Checklist = GF 8
 */

declare( strict_types = 1 );

use CraftyAnnie\Pdf;

/**
 * Convenience function to build and write a PDF from HTML.
 *
 * @param  string  $html
 *
 * @return Pdf\PdfMaker
 */

function craftyannie_create_wishlist_attachment( string $html ): Pdf\PdfMaker {
	$pdf = craftyannie_pdf_handler();
	$pdf->make( $html );
	$pdf->save( get_stylesheet_directory() . '/library/pdf/my-antalis-favourites_' . time() . '.pdf' );
	return $pdf;
}

/**
 * This will generate and attach a pdf of the resources based on the answers given by a user taking the
 *
 * @param array $entry the GF entry data.
 */
function email_checklist_after_submission( array $entry, array $form ): void {

	// Entry[22] is the email field - check if there's an email there and it's an actual email, otherwise skip pdf generation.
	if ( $entry[22] && filter_var( $entry[22], FILTER_VALIDATE_EMAIL ) ) {

		$details['pdf_entry_data'] = craftyannie_compile_pdf_entry_data( $entry, $form );

		// User details.
		$details['email'] = sanitize_email( $entry[22] );

		// Email details.
		$details['title']     = 'Ann Craft Trust - Safeguarding Checklist';
		$details['subject']   = 'Ann Craft Trust - Your Safeguarding Checklist';
		$details['template']  = 'safeguarding_checklist';
		$details['pdf_title'] = 'Your Safeguarding Checklist';


		$pdf_image_path = get_stylesheet_directory() . '/assets/images/logo.png';

		// Create the wishlist HTML.
		ob_start();
		include_once get_stylesheet_directory() . '/template-parts/pdf/safeguarding-checklist.php';
		$html = ob_get_clean();

		// Make the wishlist file.
		$pdf = craftyannie_create_wishlist_attachment( $html );

		craftyannie_send_wishlist_email( $pdf, $details );
		die;
	}


}

add_action( 'gform_after_submission_8', 'email_checklist_after_submission', 10, 2 );

/**
 * Send the wishlist email and delete the PDF file.
 *
 * @param  Pdf\PdfMaker  $pdf
 * @param  array  $details
 *
 * @return bool
 */
function craftyannie_send_wishlist_email( Pdf\PdfMaker $pdf, array $details ): bool {

	$headers   = [];
	$headers[] = 'From: Antalis Substr8 <noreply@antalis-substr8.co.uk>';

	// Get the email content.
	ob_start();
	include_once get_stylesheet_directory() . '/template-parts/email/header.php';
	include_once get_stylesheet_directory() . '/template-parts/email/' . $details['template'] . '.php';
	include_once get_stylesheet_directory() . '/template-parts/email/footer.php';
	$message = ob_get_clean();

	// Send the mail.
	$success = wp_mail( $details['email'], $details['subject'], $message, $headers, [ $pdf->get_path() ] );

	// Delete the PDF.
	$pdf->delete();

	return $success;
}

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

		// We only need to worry about radio buttons.
		if ( $form_field instanceof GF_Field_Radio ) {

			// Create an array containing the field_id and the form entry object.
			$pdf_entry_data[] = [
				"field_id" => $form_field['id'],
				"entry_object" => new Pdf\PdfEntry( $form_field['label'], $form_field['description'], $entry_data[$form_field['id']] )
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
	if ( function_exists( acf_add_options_page() ) ) {

		//Check which form we're actually doing this for.
		switch ( $form_id ) {

			// GF8 = Organisation.
			case 8:
				$organization_supporting_content = get_field( 'ac_safeguarding_organisation_supporting_content', 'option' );
				foreach ( $pdf_entry_data as $pdf_entry_datum ) {

					// Check if they have answered "no" and the organisation actually has fields with content.
					if ( mb_strtolower( $pdf_entry_datum['entry_object']->getEntryAnswer() ) === "no" && have_rows( $organization_supporting_content ) ) {
						while ( have_rows( $organization_supporting_content ) ) {
							// If the field ID exists in the list of fields.
							if ( $pdf_entry_datum["form_id"] === get_sub_field( "ac_gravity_form_field_id" ) ) {
								// Assign the support content and break out of the while.
								$pdf_entry_datum['entry_object']->setEntrySupportContent( get_sub_field( "ac_supporting_content" ) );
								break;
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
