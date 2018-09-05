<?php namespace Yoast;

/**
 * Class PullRequest
 * @package Yoast
 */
class PullRequest {

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
	 * @var string
	 */
	private $issueNumber;

	/**
	 * PullRequest constructor.
	 *
	 * @param int    $id   The id of the PullRequest.
	 * @param string $url  The url to the PullRequest.
	 * @param string $body The body of the PullRequest.
	 */
	public function __construct( $id, $url, $body ) {
		$this->id          = $id;
		$this->url         = $url;
		$this->body        = $this->extractData( $body );
		$this->issueNumber = $this->extractIssueNumber( $body );
	}

	/**
	 * Retrieves the id of the PullRequest.
	 *
	 * @return int The id of the PullRequest.
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Retrieves the url of the PullRequest.
	 *
	 * @return string The url of the PullRequest.
	 */
	public function url() {
		return $this->url;
	}

	/**
	 * Retrieves the body of the PullRequest.
	 *
	 * @return string The body of the PullRequest.
	 */
	public function body() {
		return $this->body;
	}

	/**
	 * Retrieves the issue number associated with the PullRequest.
	 *
	 * @return string The issue number associated with the PullRequest.
	 */
	public function issueNumber() {
		return $this->issueNumber;
	}

	/**
	 * Extracts the relevant data from the body text.
	 *
	 * @param string $body The body text to extract relevant data from.
	 *
	 * @return string The relevant data.
	 */
	protected function extractData( $body ) {
		preg_match_all( '/This PR can be summarized in the following changelog (?:entry|entries):\s+((?:\*\s[^\n]+\s?)+)/s',
			$body, $results, PREG_SET_ORDER );

		if ( ! isset( $results[0] ) || ! isset( $results[0][1] ) ) {
			return '';
		}

		$found_entries = $results[0][1];

		preg_match_all( '/(\*\s[^\n]+)\s/s', $found_entries, $changelog_lines );

		return implode( PHP_EOL, $changelog_lines[1] );
	}

	/**
	 * Extracts the issue number from the body text.
	 *
	 * @param string $body The body text to extract the issue number from.
	 *
	 * @return string The issue number. Returns empty string if none was found.
	 */
	private function extractIssueNumber( $body ) {
		preg_match_all( '/Fixes:?\s?#(\d+)/i', $body, $results );

		if ( ! array_key_exists( 1, $results ) || ! array_key_exists( 0, $results[1] ) ) {
			return '';
		}

		return trim( $results[1][0] );
	}
}
