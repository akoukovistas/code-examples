<?php declare( strict_types=1 );

namespace CraftyAnnie\Pdf;

use Dompdf\Dompdf;

/**
 * Class PdfMaker
 *
 * @package CraftyAnnie\Pdf
 */
class PdfMaker {

	/**
	 *
	 * @var Dompdf The DOMPDF object.
	 */
	private $dompdf;

	/**
	 *
	 * @var string The PDF filepath.
	 */
	private $path;

	/**
	 * @var string The PDF name.
	 */
	private $name;

	/**
	 * PdfMaker constructor.
	 *
	 * @param  Dompdf  $dompdf
	 */
	public function __construct( Dompdf $dompdf ) {
		$this->dompdf = $dompdf;
	}

	/**
	 * Set the filepath.
	 *
	 * @param  string  $path
	 */
	public function set_path( string $path ): void {
		$this->path = $path;
	}

	/**
	 * Return the filepath.
	 *
	 * @return string
	 */
	public function get_path(): string {
		return $this->path;
	}

	/**
	 * Make the PDF file.
	 *
	 * @param  string  $html
	 */
	public function make( string $html ): void {

		// Load HTML.
		$this->dompdf->loadHtml( $html );

		// Render to PDF.
		$this->dompdf->render();
	}

	/**
	 * Save the PDF file.
	 *
	 * @param  string  $path
	 */
	public function save( string $path ): void {

		// Set the path.
		$this->set_path( $path );

		// Create file output.
		$output = $this->dompdf->output();

		// Write output to path.
		file_put_contents( $path, $output );
	}

	/**
	 * Delete the last known path.
	 */
	public function delete() {
		if ( file_exists( $this->path ) ) {
			unlink( $this->path );
		}
	}

	/**
	 * Stream the PDF.
	 */
	public function stream(): void {
		$this->dompdf->stream( 'safeguarding checklist.pdf', [ 'Attachment' => false ] );
		exit;
	}

	/**
	 * Set the name of the PDF.
	 *
	 * @param string $name
	 */
	public function set_name( string $name ): void {
		$this->name = $name;
	}

	/**
	 * Get the name of the PDF.
	 *
	 * @return string The PDF name.
	 */
	public function get_name(): string {
		return $this->name;
	}
}
