<?php namespace Yoast\Repositories;

use GuzzleHttp\ClientInterface;

/**
 * Class PullRequestsRepository
 * @package Yoast\Repositories
 */
class PullRequestsRepository extends BaseRepository {

	/**
	 * Milestones constructor.
	 *
	 * @param ClientInterface $client The Client to use for data retrieval.
	 * @param string          $repository The repository to retrieve the data from.
	 */
	public function __construct( ClientInterface $client, $repository ) {
		parent::__construct( $client, $repository . '/issues' );
	}

	/**
	 * Retrieves issues for the given milestone id.
	 *
	 * @param int $milestoneID The milestone id to retrieve the issues for.
	 *
	 * @return array Array containing the issues for the milestone.
	 */
	public function getForMilestoneId( $milestoneID ) {
		$response = $this->getClient()->get(
			$this->getEndpoint(), [
				'query' => [
					'state'     => 'closed',
					'milestone' => $milestoneID,
					'per_page'  => 100,
				]
			] );

		return $this->decode( $response );
	}
}
