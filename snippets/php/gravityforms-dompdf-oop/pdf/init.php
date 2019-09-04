<?php

require 'vendor/autoload.php';
require 'PdfMaker.php';

/**
 * Get PDF handler.
 */
function craftyannie_pdf_handler() {
	$options = new Dompdf\Options();
	$options->set( 'isHtml5ParserEnabled', true );

	return new \CraftyAnnie\Pdf\PdfMaker( new Dompdf\Dompdf( $options ) );
}
