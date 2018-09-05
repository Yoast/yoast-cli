<?php namespace Yoast;

/**
 * Class MenuOption
 * @package Yoast
 */
class MenuOption {

	/**
	 * @var string
	 */
	private $option;

	/**
	 * @var string
	 */
	private $label;

	/**
	 * @var string
	 */
	private $endpoint;

	/**
	 * @var array
	 */
	private $dependencies;

	/**
	 * @var bool
	 */
	private $buildable;

	/**
	 * @var string
	 */
	private $mainFile;

	/**
	 * MenuOption constructor.
	 *
	 * @param string $option 		The option key.
	 * @param string $label			The option's label.
	 * @param string $endpoint		The API endpoint of the option.
	 * @param string $mainFile		The main plugin file associated with the option.
	 * @param array  $dependencies	Array of dependencies associated with the option.
	 * @param bool   $buildable		Whether or not the option should be considered buildable.
	 */
	public function __construct( $option, $label, $endpoint, $mainFile, $dependencies = [], $buildable = false ) {
		$this->option 		= $option;
		$this->label 		= $label;
		$this->endpoint 	= $endpoint;
		$this->dependencies = $dependencies;
		$this->buildable 	= $buildable;
		$this->mainFile 	= $mainFile;
	}

	/**
	 * Retrieves the passed property if it exists.
	 *
	 * @param string $property The property to retrieve.
	 *
	 * @return mixed The option property.
	 */
	public function __get( $property ) {
		return $this->$property;
	}

	/**
	 * Determines if the passed property is set.
	 *
	 * @param string $property The property to search for.
	 *
	 * @return bool Whether or not the property is set.
	 */
	public function __isset( $property ) {
		return isset( $this->$property );
	}
}
