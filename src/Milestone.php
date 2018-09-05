<?php namespace Yoast;

/**
 * Class Milestone
 * @package Yoast
 */
class Milestone {
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $label;

	/**
	 * Milestone constructor.
	 *
	 * @param int $id The id for the milestone.
	 * @param string $label The label of the milestone.
	 */
	public function __construct( $id, $label ) {
		$this->id = $id;
		$this->label = $label;
	}

	/**
	 * Retrieves the id of the milestone.
	 *
	 * @return int The milestone id.
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Retrieves the label for the milestone.
	 *
	 * @return string The milestone label.
	 */
	public function label() {
		return $this->label;
	}
}
