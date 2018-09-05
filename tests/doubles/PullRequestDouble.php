<?php

namespace Yoast\Tests\Doubles;

use Yoast\PullRequest;

class PullRequestDouble extends PullRequest {
	/**
	 * Extracts the relevant data from the body text.
	 *
	 * @param string $body The body text to extract relevant data from.
	 *
	 * @return string The relevant data.
	 */
	public function extractData( $body ) {
		return parent::extractData( $body );
	}
}
