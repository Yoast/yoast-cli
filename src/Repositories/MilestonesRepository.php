<?php namespace Yoast\Repositories;

use GuzzleHttp\ClientInterface;

/**
 * Class Milestones
 * @package Yoast\Repositories
 */
class MilestonesRepository extends BaseRepository {

	/**
	 * Milestones constructor.
	 *
	 * @param ClientInterface $client
	 * @param string          $repository
	 */
	public function __construct( ClientInterface $client, $repository ) {
		parent::__construct( $client, $repository . '/milestones' );
	}

	/**
	 * Finds a particular record by the given id.
	 *
	 * @param sting $id The id of the record to find.
	 *
	 * @return array The found record.
	 */
	public function find( $id ) {
		$milestones = $this->all();
		$milestoneID = -1;

		if ( count( $milestones ) === 0 ) {
			return $milestoneID;
		}

		foreach ( $milestones as $milestone ) {
			if ( $milestone[ 'title' ] === $id ) {
				$milestoneID = $milestone[ 'number' ];

				break;
			}
		}

		return $milestoneID;
	}

	/**
	 * Finds all the active milestones for the repository.
	 *
	 * @return array The active milestones.
	 */
	public function findActive() {
		$response = $this->getClient()->get(
			$this->getEndpoint(),
			[
				'query' => [
					'state' => 'open'
				]
			]
		);

		return $this->decode( $response );
	}
}
