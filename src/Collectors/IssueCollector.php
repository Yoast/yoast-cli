<?php namespace Yoast\Collectors;

use Yoast\Issue;
use Yoast\PullRequest;

/**
 * Class IssueCollector
 * @package Yoast\Collectors
 */
class IssueCollector extends BaseCollector {
	/**
	 * @param mixed $item
	 */
	public function add( $item ) {
		parent::add( $this->formatItem( $item ) );
	}

	/**
	 * Converts the array of PullRequest objects to an array.
	 *
	 * @return array Array containing the PullRequests.
	 */
	public function toArray() {
		$result = [];

		foreach ( $this->all() as $issue ) {
			array_push( $result, [
				'id'    => $issue->id(),
				'url'   => $issue->url(),
				'body'  => $issue->body(),
				'labels' => $issue->labels(),
			] );
		}

		return $result;
	}

	/**
	 * Formats the item if it's not a proper PullRequest object.
	 *
	 * @param array $item The array item to convert to a PullRequest (if necessary).
	 *
	 * @return PullRequest The PullRequest object.
	 */
	public function formatItem( $item ) {
		if ( $item instanceof Issue === false ) {
			$item = new Issue( $item['number'], $item['html_url'], $item['body'], $item['labels'] );
		}

		return $item;
	}

	public function getById( $id ) {
		$filtered = array_filter( $this->all(), function( $issue ) use ( $id ) {
			return $issue->id() === (int) $id;
		} );

		return array_pop( $filtered );
	}
}
