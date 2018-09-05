<?php namespace Yoast\Collectors;

use Yoast\PullRequest;

/**
 * Class PullRequestCollector
 * @package Yoast\Collectors
 */
class PullRequestCollector extends BaseCollector {

	/**
	 * Adds a batch of iterable items to the collector and converts them to PullRequest objects.
	 *
	 * @param array $items Array containing the items to add.
	 *
	 * @return void
	 */
	public function addBatch( array $items ) {
		foreach ( $items as $item ) {
			if ( ! array_key_exists( 'pull_request', $item ) ) {
				continue;
			}

			$item = $this->formatItem( $item );

			$this->add( $item );
		}
	}

	/**
	 * Converts the array of PullRequest objects to an array.
	 *
	 * @return array Array containing the PullRequests.
	 */
	public function toArray() {
		$result = [];

		foreach ( $this->all() as $pullRequest ) {
			array_push( $result, [
				'id'    => $pullRequest->id(),
				'url'   => $pullRequest->url(),
				'body'  => $pullRequest->body(),
				'issueNumber' => $pullRequest->issueNumber(),
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
		if ( $item instanceof PullRequest === false ) {
			$item = new PullRequest( $item['id'], $item['html_url'], $item['body'] );
		}

		return $item;
	}
}
