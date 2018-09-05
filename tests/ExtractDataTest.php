<?php

use PHPUnit\Framework\TestCase;
use Yoast\Tests\Doubles\PullRequestDouble;

/**
 * Unit Test Class.
 */
final class ExtractDataTest extends TestCase {

	/**
	 * @covers PullRequest::extractData()
	 */
	public function testDataExtractorSingleLine() {
		$input = '
This PR can be summarized in the following changelog entry:

* Removes all uses of meta keywords throughout the plugin.

Hello world
';

		$instance = new PullRequestDouble( 1, 1, '' );

		$result = $instance->extractData( $input );

		$this->assertContains( '* Removes all uses of meta keywords throughout the plugin.', $result );

	}

	/**
	 * @covers PullRequest::extractData()
	 */
	public function testDataExtractorMultiLine() {
		$input = '
This PR can be summarized in the following changelog entries:
* First line.
* Second line.
* Third line.
';

		$instance = new PullRequestDouble( 1, 1, '' );

		$result = $instance->extractData( $input );

		$this->assertContains( '* First line.', $result );
		$this->assertContains( '* Second line.', $result );
		$this->assertContains( '* Third line.', $result );
	}

	/**
	 * @covers PullRequest::extractData()
	 */
	public function testDataExtractorMultiLineAndNewSubject() {
		$input = '
This PR can be summarized in the following changelog entry:

* First line.
* Second line.
* Third line.
#h*llo w*rld
';

		$instance = new PullRequestDouble( 1, 1, '' );

		$result = $instance->extractData( $input );

		$this->assertContains( '* First line.', $result );
		$this->assertContains( '* Second line.', $result );
		$this->assertContains( '* Third line.', $result );
		$this->assertNotContains( '#h*llo w*rld', $result );
	}
}
