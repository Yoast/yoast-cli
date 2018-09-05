<?php namespace Yoast\Collectors;

use Yoast\Milestone;

/**
 * Class MilestoneCollector
 * @package Yoast\Collectors
 */
class MilestoneCollector extends BaseCollector {

	/**
	 * Adds a batch of iterable items to the collector and converts them to Milestone objects.
	 *
	 * @param array $items Array containing the items to add.
	 *
	 * @return void
	 */
	public function addBatch( array $items ) {
		foreach ( $items as $item ) {
			$item = $this->formatItem( $item );

			$this->add( $item );
		}
	}

	/**
	 * Converts the array of Milestone objects to an array.
	 *
	 * @return array Array containing the Milestones.
	 */
	public function toArray() {
		$result = [];

		foreach ( $this->all() as $milestone ) {
			array_push( $result, [
				'id'    => $milestone->id(),
				'label' => $milestone->label()
			] );
		}

		return $result;
	}

	/**
	 * Gets all the Milestones with a particular label.
	 *
	 * TODO: Look into a cleaner option than this, as we only want a single item and not an array.
	 *
	 * @param string $label The label to look for.
	 *
	 * @return array Array containing the found Milestones. Generally this should only be a single item.
	 */
	public function getByLabel( $label ) {
		return array_values( array_filter( $this->all(), function( $milestone ) use ( $label ) {
			return $milestone->label() === $label;
		} ) );
	}

	/**
	 * Formats the item if it's not a proper Milestone object.
	 *
	 * @param array $item The array item to convert to a Milestone (if necessary).
	 *
	 * @return Milestone The Milestone object.
	 */
	protected function formatItem( $item ) {
		if ( $item instanceof Milestone === false ) {
			$item = new Milestone( $item['number'], $item['title'] );
		}

		return $item;
	}
}
