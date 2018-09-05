<?php namespace Yoast\Repositories;

use GuzzleHttp\ClientInterface;

/**
 * Class IssuesRepository
 * @package Yoast\Repositories
 */
class IssuesRepository extends BaseRepository {

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

	/**
	 * Retrieves an issue by it's issue number.
	 *
	 * @param string $issueId The issue ID to query.
	 *
	 * @return array The issue found for the particular issue.
	 */
	public function getIssue( $issueId ) {
		$response = $this->getClient()->get( $this->getEndpoint() . '/' . $issueId );

		return $this->decode( $response );
	}

	/**
	 * Retrieves the labels associated with the given issue ID.
	 *
	 * @param string $issueId The issue ID to query.
	 *
	 * @return array The labels found for the particular issue.
	 */
	public function getLabels( $issueId ) {
		$response = $this->getClient()->get( $this->getEndpoint() . '/' . $issueId . '/labels' );

		return $this->decode( $response );
	}
}
