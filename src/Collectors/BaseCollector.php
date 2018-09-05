<?php namespace Yoast\Collectors;

/**
 * Class BaseCollector
 * @package Yoast\Collectors
 */
class BaseCollector {
	/**
	 * @var array Array containing all the added items.
	 */
	private $items = [];

	/**
	 * Adds a batch of iterable items to the collector.
	 *
	 * @param array $items Array containing the items to add.
	 *
	 * @return void
	 */
	public function addBatch( array $items ) {
		foreach ( $items as $item ) {
			$this->add( $item );
		}
	}

	/**
	 * Adds an item to the collection.
	 *
	 * @param mixed $item The item to add.
	 *
	 * @return void
	 */
	public function add( $item ) {
		array_push( $this->items, $item );
	}

	/**
	 * Retrieves all the items from the collection.
	 *
	 * @return array The items in the collection.
	 */
	public function all() {
		return $this->items;
	}

	/**
	 * Returns the length of the collection.
	 *
	 * @return int The length of the collection.
	 */
	public function count() {
		return count( $this->items );
	}
}
