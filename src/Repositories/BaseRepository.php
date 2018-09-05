<?php namespace Yoast\Repositories;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Class BaseRepository
 * @package Yoast\Repositories
 */
class BaseRepository implements RepositoryInterface {
	/**
	 * @var ClientInterface
	 */
	private $client;

	/**
	 * @var string
	 */
	private $endpoint;

	/**
	 * BaseRepository constructor.
	 *
	 * @param ClientInterface $client The Client to use for data retrieval.
	 * @param string          $endpoint The endpoint to retrieve the data from.
	 */
	public function __construct( ClientInterface $client, $endpoint ) {
		$this->client = $client;
		$this->endpoint = $endpoint;
	}

	/**
	 * Retrieves all the available records for the repository.
	 *
	 * @return array All the records that could be found.
	 */
	public function all() {
		$response = $this->client->get( $this->getEndpoint() );

		return $this->decode( $response );
	}

	/**
	 * Gets the client.
	 *
	 * @return ClientInterface The client.
	 */
	public function getClient() {
		return $this->client;
	}

	/**
	 * Determines whether or not the repository has a client set.
	 *
	 * @return boolean Whether or not the client has been set.
	 */
	public function hasClient() {
		return $this->client !== null;
	}

	/**
	 * Gets the endpoint for the repository.
	 *
	 * @return string The endpoint for the interface.
	 */
	public function getEndpoint() {
		return $this->endpoint;
	}

	/**
	 * Finds a particular record by the given id.
	 *
	 * @param sting $id The id of the record to find.
	 *
	 * @return array The found record.
	 */
	public function find( $id ) {
		return array();
	}

	/**
	 * Decodes the Response object to an array.
	 *
	 * @param Response $response The Reponse to decode.
	 *
	 * @return array The decoded response.
	 */
	protected function decode( Response $response ) {
		return json_decode( (string) $response->getBody(), true );
	}
}
