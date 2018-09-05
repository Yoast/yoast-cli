<?php namespace Yoast\Errors;

/**
 * Custom exception to be used if the required .env file is missing.
 *
 * @package Yoast\Errors
 */
class MissingEnvironmentFileException extends \RuntimeException {

	/**
	 * MissingEnvironmentFileException constructor.
	 */
	public function __construct() {
		parent::__construct(
			sprintf(
				'No .env file was found. Please ensure that the file is present before running this script. Read %s for more information on how to add a .env file' . PHP_EOL,
				'https://github.com/Yoast/Wiki/wiki/Yoast-CLI#creating-beta-versions-currently-not-usable-for-rcs'
			)
		);
	}
}
