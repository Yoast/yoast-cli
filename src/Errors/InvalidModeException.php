<?php namespace Yoast\Errors;

/**
 * Custom exception to be used if a mode (i.e. verbose mode) isn't supported.
 *
 * @package Yoast\Errors
 */
class InvalidModeException extends \RuntimeException {

	/**
	 * InvalidModeException constructor.
	 */
	public function __construct() {
		parent::__construct( 'You cannot run this script in the selected mode' );
	}
}
