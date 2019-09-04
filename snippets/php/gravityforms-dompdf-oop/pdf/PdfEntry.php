<?php declare( strict_types=1 );

	namespace CraftyAnnie\Pdf;

	/**
	 * Class PdfEntry
	 * @package CraftyAnnie\Pdf
	 */
	class PdfEntry {

		// The variables for our object.
		private $entry_label;
		private $entry_description;
		private $entry_answer;
		private $entry_support_content;

		/**
		 * PdfEntry constructor.
		 * @param $entry_label
		 * @param $entry_description
		 * @param $entry_answer
		 * @param $entry_support_content
		 */
		public function __construct( $entry_label, $entry_description, $entry_answer = "", $entry_support_content="" ){

			$this->entry_label = $entry_label;
			$this->entry_description = $entry_description;
			$this->entry_answer = $entry_answer;
			$this->entry_support_content = $entry_support_content;

		}

		/**
		 * @return mixed
		 */
		public function getEntryLabel()
		{
			return $this->entry_label;
		}

		/**
		 * @param mixed $entry_label
		 */
		public function setEntryLabel($entry_label): void
		{
			$this->entry_label = $entry_label;
		}

		/**
		 * @return mixed
		 */
		public function getEntryDescription()
		{
			return $this->entry_description;
		}

		/**
		 * @param mixed $entry_description
		 */
		public function setEntryDescription($entry_description): void
		{
			$this->entry_description = $entry_description;
		}

		/**
		 * @return mixed
		 */
		public function getEntryAnswer()
		{
			return $this->entry_answer;
		}

		/**
		 * @param mixed $entry_answer
		 */
		public function setEntryAnswer($entry_answer): void
		{
			$this->entry_answer = $entry_answer;
		}

		/**
		 * @return mixed
		 */
		public function getEntrySupportContent()
		{
			return $this->entry_support_content;
		}

		/**
		 * @param mixed $entry_support_content
		 */
		public function setEntrySupportContent($entry_support_content): void
		{
			$this->entry_support_content = $entry_support_content;
		}
	}
