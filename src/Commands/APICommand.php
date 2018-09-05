<?php namespace Yoast\Commands;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;

/**
 * Class APICommand
 * @package Yoast\Command
 */
class APICommand extends Command {

	/**
	 * @var Client
	 */
	protected $client;

	/**
	 * APICommand constructor.
	 */
	public function __construct() {
		parent::__construct();

		$token = getenv( 'GITHUB_API_TOKEN' );

		if ( $token === '' ) {
			throw new \Error( 'No GitHub access token set' );
		}

		$this->client = new Client( [
			'base_uri' => 'https://api.github.com/repos/Yoast/',
			'headers' => [
				'Authorization' => 'token ' . $token
			],
		] );
	}
}
