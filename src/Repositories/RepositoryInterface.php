<?php namespace Yoast\Repositories;

use GuzzleHttp\ClientInterface;

/**
 * Interface RepositoryInterface
 * @package Yoast\Repositories
 */
interface RepositoryInterface {

	/**
	 * Finds a particular record by the given id.
	 *
	 * @param sting $id The id of the record to find.
	 *
	 * @return array The found record.
	 */
	public function find( $id );

	/**
	 * Retrieves all the available records for the repository.
	 *
	 * @return array All the records that could be found.
	 */
	public function all();

	/**
	 * Gets the client.
	 *
	 * @return ClientInterface The client.
	 */
	public function getClient();

	/**
	 * Determines whether or not the repository has a client set.
	 *
	 * @return boolean Whether or not the client has been set.
	 */
	public function hasClient();

	/**
	 * Gets the endpoint for the repository.
	 *
	 * @return string The endpoint for the interface.
	 */
	public function getEndpoint();
}
