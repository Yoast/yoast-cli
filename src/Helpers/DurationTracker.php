<?php namespace Yoast\Helpers;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * Class DurationTracker
 * @package Yoast\Helpers
 */
class DurationTracker {

	/**
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * @var Stopwatch
	 */
	private $stopwatch;

	/**
	 * DurationTracker constructor.
	 */
	public function __construct() {
		$this->filesystem = new Filesystem();
		$this->stopwatch  = new Stopwatch();
	}

	/**
	 * Starts the stopwatch.
	 *
	 * @param string $name The named stopwatch to start.
	 *
	 * @return void
	 */
	public function start( $name ) {
		$this->stopwatch->start( $name );
	}

	/**
	 * Stops the stopwatch.
	 *
	 * @param string $name The named stopwatch to stop.
	 *
	 * @return \Symfony\Component\Stopwatch\StopwatchEvent The stopwatch event.
	 */
	public function stop( $name ) {
		return $this->stopwatch->stop( $name );
	}

	/**
	 * Returns an event duration in seconds.
	 *
	 * @param StopwatchEvent $event The event to get the duration from.
	 *
	 * @return float|int The duration.
	 */
	public function getDuration( StopwatchEvent $event ) {
		return ( $event->getDuration() / 1000 );
	}

	/**
	 * Saves the total duration of the build.
	 *
	 * @param StopwatchEvent $event The event to save.
	 *
	 * @return void
	 */
	public function saveTotalDuration( StopwatchEvent $event ) {
		$this->filesystem->dumpFile( '.executionTime',  $this->getDuration( $event ) );
	}

	/**
	 * Gets the saved total duration from storage.
	 *
	 * @return float The total duration.
	 */
	public function getSavedTotalDuration() {
		if ( ! $this->filesystem->exists( '.executionTime' ) ) {
			return 0.00;
		}

		return file_get_contents( '.executionTime' );
	}
}
