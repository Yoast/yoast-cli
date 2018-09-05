<?php namespace Yoast\Commands;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Yaml\Yaml;

use Yoast\Collectors\IssueCollector;
use Yoast\Errors\InvalidModeException;
use Yoast\Writers\Markdown;
use Yoast\Collectors\MilestoneCollector;
use Yoast\Collectors\PullRequestCollector;
use Yoast\Repositories\IssuesRepository;
use Yoast\Repositories\MilestonesRepository;

/**
 * Class CreateChangelogCommand
 * @package Yoast\Commands
 */
class CreateChangelogCommand extends APICommand {

	/**
	 * @var MilestoneCollector
	 */
	private $milestoneCollector;

	/**
	 * @var PullRequestCollector
	 */
	private $pullRequestCollector;

	/**
	 * @var string
	 */
	private $destination;

	/**
	 * @var IssueCollector
	 */
	private $issueCollector;

	/**
	 * @var IssuesRepository
	 */
	private $issueRepository;

	/**
	 * CreateChangelogCommand constructor.
	 */
	public function __construct() {
		parent::__construct();

		// Note that dirname( __FILE__, 3) is a PHP7+ function. This will not work with lower PHP versions.
		$this->destination = dirname( __FILE__, 3 ) . '/changelogs';

		$this->milestoneCollector   = new MilestoneCollector();
		$this->pullRequestCollector = new PullRequestCollector();
		$this->issueCollector       = new IssueCollector();
	}

	/**
	 * Configures the command.
	 *
	 * @return void
	 */
	protected function configure() {
		$this->setName( 'changelog:create' )
		     ->setDescription( 'Creates a changelog for the passed milestone.' )
			->setHelp( 'Allows you to create changelogs for the plugins.' );
	}

	/**
	 * Executes the command.
	 *
	 * @param InputInterface  $input  The input interface to use to handle incoming information from the CLI.
	 * @param OutputInterface $output The output interface to use to write information to the CLI.
	 *
	 * @return void
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {

		// Force the command to always be interactive.
		$input->setInteractive( true );

		if ( $output->isQuiet() || $output->isVerbose() || $output->isVeryVerbose() || $output->isDebug() ) {
			throw new InvalidModeException();
			exit();
		}

		$selectedRepository    = $this->getRepositoryEndpoint( $this->listRepositoryOptions( $input, $output ) );
		$this->issueRepository = new IssuesRepository( $this->client, $selectedRepository );

		$milestoneRepository = new MilestonesRepository( $this->client, $selectedRepository );
		$activeMilestones    = $milestoneRepository->findActive();
		$this->milestoneCollector->addBatch( $activeMilestones );

		$selectedMilestone = $this->listMilestoneOptions( $input, $output );

		$this->collectPullRequests( $selectedMilestone->id() );
		$this->collectIssues( $this->pullRequestCollector->all() );

		$fileDestination = $this->outputToFile( $selectedRepository, $selectedMilestone );

		$output->writeln( "Changelog generation complete" );
		$output->writeln( sprintf( "Changelog items can be found in: %s", $fileDestination ) );
	}

	/**
	 * Collects pull requests for the given milestone.
	 *
	 * @param $milestone The milestone to collect the pull requests for.
	 *
	 * @return void
	 */
	protected function collectPullRequests( $milestone ) {
		// This assumes we're only dealing with milestoned PRs. This might need fixing.
		$this->pullRequestCollector->addBatch(
			$this->issueRepository->getForMilestoneId( $milestone )
		);
	}

	/**
	 * Retrieves the issues associated with the passed pull requests.
	 *
	 * @param array $pullRequests The pull requests to get the issues for.
	 *
	 * @return void
	 */
	protected function collectIssues( $pullRequests ) {
		foreach ( $pullRequests as $pullRequest ) {
			if ( $pullRequest->issueNumber() === "" ) {
				continue;
			}

			$this->issueCollector->add(
				$this->issueRepository->getIssue( $pullRequest->issueNumber() )
			);
		}
	}

	/**
	 * Outputs the found data and turns it into a Markdown file.
	 *
	 * @param string $repository The repository to display in the file.
	 * @param string $milestone  The milestone to display in the file.
	 *
	 * @return string The path to the outputted file.
	 */
	protected function outputToFile( $repository, $milestone ) {
		$output = sprintf(
					  '# %s: %s - %d changelog items',
			          $repository,
			          $milestone->label(),
			          $this->pullRequestCollector->count()
		          ) . "\n\n";

		$bugOutput         = "## Bugs:\n\n";
		$enhancementOutput = "## Enhancements:\n\n";
		$otherOutput       = "## Other:\n\n";

		foreach ( $this->pullRequestCollector->all() as $item ) {
			$issue = $this->issueCollector->getById( $item->issueNumber() );

			// Extract labels
			$format  = "[%s](%s)\n%s";
			$lineEnd = "\n\n";
			if ( empty( $issue ) ) {
				$otherOutput .= sprintf( $format, $item->url(), $item->url(), $item->body() ) . $lineEnd;
				continue;
			}

			if ( in_array( 'bug', $issue->labels(), true ) ) {
				$bugOutput .= sprintf( $format, $item->issueNumber(), $item->url(), $item->body() ) . $lineEnd;
				continue;
			}

			if ( in_array( 'enhancement', $issue->labels(), true ) ) {
				$enhancementOutput .= sprintf( $format, $item->issueNumber(), $item->url(), $item->body() ) . $lineEnd;
				continue;
			}

			$otherOutput .= sprintf( $format, $item->issueNumber(), $item->url(), $item->body() ) . $lineEnd;

		}

		$output .= $bugOutput . $enhancementOutput . $otherOutput;

		$markdown   = new Markdown( $this->destination );
		$outputFile = $this->destination . '/' . $this->generateFileName( $repository, $milestone );
		$markdown->write( $outputFile, $output );

		return $outputFile;
	}

	/**
	 * Generates a properly formatted filename for the changelog.
	 *
	 * @param string    $repository The repository to use as part of the file name.
	 * @param Milestone $milestone  The milestone to use.
	 *
	 * @return string The file name.
	 */
	protected function generateFileName( $repository, $milestone ) {
		return sprintf( 'changelog-%s-%s.md', $repository, $milestone->label() );
	}

	/**
	 * Converts the plugins.yml file to an associative array.
	 *
	 * @return array Array containing the available repositories and their properties.
	 */
	private function getAvailableRepositories() {
		return Yaml::parseFile( __DIR__ . '/../../configs/plugins.yaml' );
	}

	/**
	 * Retrieves the repository's endpoint.
	 *
	 * @param string $repository The repository to get the endpoint for.
	 *
	 * @return string The repository's endpoint.
	 */
	protected function getRepositoryEndpoint( $repository ) {
		$endpoints = $this->mapOptionsAndEndpoints();

		if ( ! array_key_exists( $repository, $endpoints ) ) {
			throw new InvalidArgumentException( 'Repository doesn\'t exist' );
		}

		return $endpoints[ $repository ];
	}

	/**
	 * Lists the repository options within the console.
	 *
	 * @param InputInterface  $input  The input interface that the user can interact with.
	 * @param OutputInterface $output The output interface that can send responses to the user.
	 *
	 * @return mixed The users' answer.
	 */
	protected function listRepositoryOptions( InputInterface $input, OutputInterface $output ) {
		$repositories = $this->mapOptionsAndRepositories();
		$helper       = $this->getHelper( 'question' );

		$question = new ChoiceQuestion(
			'Please select the repository you want to generate the changelog for',
			$repositories
		);

		$question->setErrorMessage( 'Option %s is invalid.' );

		return $helper->ask( $input, $output, $question );
	}

	/**
	 * Maps the options and the associated repository to a key-value pair.
	 *
	 * @return array Array containing the mapped options and repositories.
	 */
	protected function mapOptionsAndRepositories() {
		return array_combine(
			array_column( $this->getAvailableRepositories(), 'option' ),
			array_keys( $this->getAvailableRepositories() )
		);
	}

	/**
	 * Maps the options and the associated endpoint to a key-value pair.
	 *
	 * @return array Array containing the mapped options and endpoints.
	 */
	protected function mapOptionsAndEndpoints() {
		return array_combine(
			array_column( $this->getAvailableRepositories(), 'option' ),
			array_column( $this->getAvailableRepositories(), 'endpoint' )
		);
	}

	/**
	 * Lists the milestone options within the console.
	 *
	 * @param InputInterface  $input  The input interface that the user can interact with.
	 * @param OutputInterface $output The output interface that can send responses to the user.
	 *
	 * @return mixed The users' answer.
	 */
	protected function listMilestoneOptions( InputInterface $input, OutputInterface $output ) {
		$helper = $this->getHelper( 'question' );

		$question = new ChoiceQuestion(
			'Please select the milestone you want to generate the changelog for',
			array_column( $this->milestoneCollector->toArray(), 'label' )
		);

		$question->setErrorMessage( 'Option %s is invalid.' );
		$option = $helper->ask( $input, $output, $question );

		return $this->milestoneCollector->getByLabel( $option )[0];
	}
}
