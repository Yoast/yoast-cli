<?php namespace Yoast;

/**
 * Class Issue
 * @package Yoast
 */
class Issue {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $body;

	/**
	 * @var array
	 */
	private $labels;

	/**
	 * Issue constructor.
	 *
	 * @param int $id The id of the Issue.
	 * @param string $url The url to the Issue.
	 * @param string $body The body of the Issue.
	 * @param array $labels The labels associated with the Issue.
	 */
	public function __construct( $id, $url, $body, $labels ) {
		$this->id = $id;
		$this->url = $url;
		$this->body = $body;
		$this->labels = $this->formatLabels( $labels );
	}

	/**
	 * Retrieves the id of the Issue.
	 *
	 * @return int The id of the Issue.
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Retrieves the url of the Issue.
	 *
	 * @return string The url of the Issue.
	 */
	public function url() {
		return $this->url;
	}

	/**
	 * Retrieves the body of the Issue.
	 *
	 * @return string The body of the Issue.
	 */
	public function body() {
		return $this->body;
	}

	/**
	 * Retrieves the labels of the Issue.
	 *
	 * @return array The labels of the Issue.
	 */
	public function labels() {
		return $this->labels;
	}

	/**
	 * Formats the associated labels.
	 *
	 * @param array $labels The labels to format.
	 *
	 * @return array The formatted labels.
	 */
	private function formatLabels( $labels ) {
		if ( empty( $labels ) ) {
			return $labels;
		}

		return array_column( $labels, 'name' );
	}
}
