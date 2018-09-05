<?php namespace Yoast\Errors;

/**
 * Custom exception to be used if a file already exists.
 *
 * @package Yoast\Errors
 */
class FileAlreadyExistsException extends \RuntimeException {

	/**
	 * InvalidModeException constructor.
	 *
	 * @param string $file The file that already exists.
	 */
	public function __construct( $file ) {
		parent::__construct( "The file $file already exists. Please remove this file or choose a different name" );
	}
}
