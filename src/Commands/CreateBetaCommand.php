<?php namespace Yoast\Commands;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Yoast\Errors\FileAlreadyExistsException;
use Yoast\Errors\InvalidModeException;
use Yoast\Helpers\DurationTracker;
use Yoast\Menu;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateBetaCommand.
 *
 * @package Yoast\Command
 */
class CreateBetaCommand extends Command {

	/**
	 * @var Menu
	 */
	private $menu;

	/**
	 * @var string
	 */
	private $repository;

	/**
	 * @var string
	 */
	private $pluginBranch;

	/**
	 * @var array
	 */
	private $dependencyBranches = [];

	/**
	 * @var string
	 */
	private $zipName;

	/**
	 * @var string
	 */
	private $buildDirectory;

	/**
	 * @var string
	 */
	private $zipsDirectory;

	/**
	 * @var string
	 */
	private $workingDirectory;

	/**
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * @var SymfonyStyle
	 */
	private $CLIPresenter;

	/**
	 * @var string
	 */
	private $premiumDirectory;

	/**
	 * @var DurationTracker
	 */
	private $durationTracker;

	/**
	 * CreateBetaCommand constructor.
	 *
	 * @param Menu $menu
	 *
	 * @throws \Error
	 */
	public function __construct( Menu $menu ) {
		parent::__construct();

		$this->menu = $menu;

		$this->setup();
	}

	/**
	 * Sets up the initial paths and Filesystem class.
	 */
	protected function setup() {
		$this->filesystem 	  	= new Filesystem();
		$this->durationTracker  = new DurationTracker();

		$this->workingDirectory = getcwd();
		$this->buildDirectory 	= $this->workingDirectory . '/tmp';
		$this->premiumDirectory = $this->buildDirectory . '/premium';
		$this->zipsDirectory  	= $this->workingDirectory . '/zips';
	}

	/**
	 * Configures the command.
	 *
	 * @return void
	 */
	protected function configure() {
		$this->setName( 'beta' )
			 ->setDescription( 'Creates a beta for the passed repositories.' )
			 ->setHelp( 'Allows you to create versions of the plugin with various, specific versions of our dependencies. Please note that this is for testing purposes only. If you want to exit the script at any time, just use CTRL+C.' );
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
		$this->clearOutput( $output );

		// Force the command to always be interactive.
		$input->setInteractive( true );

		if ( $output->isQuiet() || $output->isVerbose() || $output->isVeryVerbose() || $output->isDebug() ) {
			throw new InvalidModeException();
			exit();
		}

		$this->CLIPresenter       = $this->getCLIPresenter( $input, $output );
		$this->repository         = $this->getRepository();
		$this->pluginBranch       = $this->chooseBranch( $this->repository->label, true );
		$this->dependencyBranches = $this->determineDependencyBranches( $this->repository->dependencies );

		$this->zipName            = $this->chooseName();

		// Selected everything we need. Time to build!
		if ( $this->confirmChoices() === true ) {
			$this->CLIPresenter->note(
				sprintf( 'Starting build. Your previous build took %s', $this->durationTracker->getSavedTotalDuration() )
			);

			$this->build();
		}
	}

	/**
	 * Gets the selected repository.
	 *
	 * @return \Yoast\MenuOption The menu option that was selected by the user.
	 */
	protected function getRepository() {
		return $this->menu->findByOption( $this->listMenuOptions() );
	}

	/**
	 * Gets the CLI presenter class.
	 *
	 * @param InputInterface  $input  The input interface to use to handle incoming information from the CLI.
	 * @param OutputInterface $output The output interface to use to write information to the CLI.
	 *
	 * @return SymfonyStyle The CLI presenter.
	 */
	protected function getCLIPresenter( InputInterface $input, OutputInterface $output ) {
		return new SymfonyStyle( $input, $output );
	}

	/**
	 * Determines what custom branches need to be used for dependencies such as YoastSEO.js and Yoast Components.
	 *
	 * @param array $dependencies The dependencies that can have a custom branch set.
	 *
	 * @return array Array containing the preferred dependency branches.
	 */
	protected function determineDependencyBranches( $dependencies ) {
		$dependencyBranches = [];

		if ( count( $dependencies ) === 0 ) {
			return $dependencyBranches;
		}

		foreach ( $dependencies as $dependency ) {
			$question = $this->chooseBranch( $dependency );

			if ( isset( $question ) && $question !== '' ) {
				$dependencyBranches[ $dependency ] = $question;
			}
		}

		return $dependencyBranches;
	}

	/**
	 * Builds the zip after cloning the right branches and installing all dependencies.
	 *
	 * @return void
	 */
	protected function build() {
		$this->durationTracker->start( 'build' );

		// Ensure that the directory doesn't already exist to prevent errors. If it does, remove it.
		if ( $this->filesystem->exists( $this->buildDirectory ) ) {
			$this->cleanup( $this->buildDirectory );
		}

		$this->cloneRepository( $this->repository->endpoint, $this->pluginBranch, $this->buildDirectory );
		$this->installDependencies();
		$this->createZip( $this->zipName );
		$this->cleanup( $this->buildDirectory );

		$this->openDir( 'zips' );

		$event = $this->durationTracker->stop( 'build' );
		$this->successMessage( 'Build complete.', $event );

		$this->durationTracker->saveTotalDuration( $event );
	}

	/**
	 * Opens the passed directory if it exists.
	 *
	 * @param $directory The directory to open.
	 *
	 * @return void
	 */
	protected function openDir( $directory ) {
		if ( ! $this->filesystem->exists( $directory ) ) {
			return;
		}

		$this->executeCommand(
			sprintf( 'open %s', $directory )
		);
	}

	/**
	 * Cleans up the passed directory by removing it.
	 *
	 * @param string $directory The directory to remove.
	 *
	 * @return void
	 */
	protected function cleanup( $directory ) {
		$this->filesystem->remove( $directory );
	}

	/**
	 * Executes the command.
	 *
	 * @param string $command 	The command to execute.
	 * @param string $cwd 		The current working directory.
	 * @param bool   $mustRun 	Whether or not the command must successfully be run.
	 *
	 * @return Process The Process.
	 */
	protected function executeCommand( $command, $cwd = '', $mustRun = true ) {
		$process = new Process( $command );
		$process->setTimeout( 3600 );

		if ( $cwd === '' ) {
			$cwd = getcwd();
		}

		$process->setWorkingDirectory( $cwd );

		if ( $mustRun === false ) {
			$process->run();
		}

		if ( $mustRun === true ) {
			$process->mustRun();
		}

		return $process;
	}

	/**
	 * Determines whether the current repository is Yoast SEO Premium.
	 *
	 * @return bool Whether or not the current repository is Yoast SEO Premium.
	 */
	private function isPremium() {
		return $this->repository->label === 'Yoast SEO Premium';
	}

	/**
	 * Installs all the Composer and JavaScript dependencies.
	 *
	 * @return void
	 */
	protected function installDependencies() {
		$this->installComposerDependencies();
		$this->installCoreJavaScriptDependencies();

		if ( $this->isPremium() ) {
			$this->installPremiumJavaScriptDependencies( $this->dependencyBranches );
		}
	}

	/**
	 * Installs Composer dependencies.
	 *
	 * @return void
	 */
	protected function installComposerDependencies() {
		$this->CLIPresenter->note( 'Installing Composer dependencies' );

		$this->durationTracker->start( 'composerDependencies' );
		$this->executeCommand( 'composer install', $this->buildDirectory );
		$event = $this->durationTracker->stop( 'composerDependencies' );

		$this->successMessage( 'Installation complete.', $event );
	}

	/**
	 * Installs core JavaScript dependencies.
	 *
	 * @return void
	 */
	protected function installCoreJavaScriptDependencies() {
		$this->CLIPresenter->note( 'Installing JavaScript dependencies' );

		$this->durationTracker->start( 'installJavaScriptDependencies' );
		$this->installJavaScriptDependencies( $this->dependencyBranches, $this->buildDirectory );
		$event = $this->durationTracker->stop( 'installJavaScriptDependencies' );

		$this->successMessage( 'Successfully installed JavaScript dependencies.', $event );
	}

	protected function prepare_dependency_directory( $dependency, $directory ) {
		if ( $dependency === 'yoast-components' && $directory === $this->premiumDirectory ) {
			return;
		}

		if ( $dependency === 'yoastseo.js' ) {
			$this->removeDependency( 'yoastseo', $directory );

			return;
		}

		$this->removeDependency( $dependency, $directory );
	}

	protected function bump_yoastseo_in_components( $branch, $directory ) {
		$installDirectory = $directory . '/node_modules/yoast-components/';

		$this->prepare_dependency_directory( 'yoastseo.js', $directory );
		$this->installCustomJavaScriptDependency( 'yoastseo.js', $branch, $installDirectory );

		$this->executeCommand( 'yarn', $installDirectory );

		return $this->executeCommand( 'grunt build', $installDirectory );
	}

	protected function build_dependency( $dependency, $directory ) {
		$installDirectory = $directory . '/node_modules/' . $dependency;

		if ( $dependency === 'yoastseo.js' ) {
			$installDirectory = $directory . '/node_modules/yoastseo';
		}

		$this->executeCommand( 'yarn', $installDirectory );

		return $this->executeCommand( 'grunt build', $installDirectory );
	}

	/**
	 * Installs a custom Yoast JavaScript dependency based on the passed arguments.
	 *
	 * @param string $dependency The dependency to install.
	 * @param string $branch	 The branch to install.
	 * @param string $directory  The directory to install into.
	 *
	 * @return Process The process that installed the dependency.
	 */
	protected function installCustomJavaScriptDependency( $dependency, $branch, $directory ) {
		if ( $dependency === 'yoast-components' && array_key_exists( 'yoastseo.js', $this->dependencyBranches ) ) {
			$command = $this->bump_yoastseo_in_components( $branch, $directory );
		} else {
			$this->prepare_dependency_directory( $dependency, $directory );

			$command = $this->addCustomYoastJavaScriptDependency( $dependency, $branch, $directory );
		}

		if ( $command->isSuccessful() ) {
			$command = $this->build_dependency( $dependency, $directory );
		}

		return $command;
	}

	/**
	 * Adds a custom Yoast JavaScript dependency based on the passed arguments.
	 *
	 * @param string $dependency The dependency to install.
	 * @param string $branch	 The branch to install.
	 * @param string $directory  The directory to install into.
	 *
	 * @return Process The process that added the dependency.
	 */
	protected function addCustomYoastJavaScriptDependency( $dependency, $branch, $directory ) {
		return $this->executeCommand(
			sprintf( 'yarn add "git+https://github.com/Yoast/%s#%s"', $dependency, $branch ),
			$directory
		);
	}

	/**
	 * Installs the customized dependency branches.
	 *
	 * @param array 	$dependencyBranches The dependency branches to install.
	 * @param string 	$directory The directory to execute the command in.
	 *
	 * @return void
	 */
	protected function installJavaScriptDependencies( $dependencyBranches, $directory ) {

		// Install all dependencies first.
		$this->executeCommand( 'yarn', $directory );

		foreach ( $dependencyBranches as $dependency => $branch ) {
			// If for some reason, we end up with an empty branch, skip onto the next item in the array.
			if ( $branch === '' ) {
				continue;
			}

			$command = $this->installCustomJavaScriptDependency( $dependency, $branch, $directory );

			if ( $command->isSuccessful() === false ) {
				throw new \RuntimeException( sprintf( 'Installation of JavaScript dependency %s failed', $dependency ) );
			}
		}
	}

	/**
	 * Installs the JavaScript dependencies in the Premium directory.
	 *
	 * @param array $dependencyBranches The dependency branches to install.
	 *
	 * @return void
	 */
	protected function installPremiumJavaScriptDependencies( $dependencyBranches ) {
		$this->CLIPresenter->note( 'Installing Premium JavaScript dependencies' );

		$this->durationTracker->start( 'installPremiumJavaScriptDependencies' );

		$this->installJavaScriptDependencies( $dependencyBranches, $this->premiumDirectory );
		$event = $this->durationTracker->stop( 'installPremiumJavaScriptDependencies' );

		$this->successMessage( 'Successfully installed JavaScript dependencies.', $event );
	}

	/**
	 * Renames the plugin files to contain the passed name/version.
	 *
	 * @param string $version The version to assign to the plugin.
	 *
	 * @return void
	 */
	protected function renamePlugin( $version ) {
		$replaceString = sprintf(
			'* Plugin Name: %s (beta) | %s | %s | %s',
			$this->repository->label,
			$this->pluginBranch,
			$version,
			date( 'd-m-Y H:m' )
		);

		$this->executeCommand(
			sprintf( 'sed -i.bak "s#%s#%s#" %s', '[Pp]lugin [Nn]ame.*',
				$replaceString,
				$this->repository->mainFile
			),
			$this->buildDirectory
		);
	}

	/**
	 * Sets the name/version for the plugin.
	 *
	 * @param string $version The version to assign to the plugin.
	 *
	 * @return void
	 */
	protected function setVersion( $version ) {
		$this->renamePlugin( $version );
		$this->setGruntVersion( $version, $this->buildDirectory );

		if ( $this->isPremium() ) {
			$this->setGruntVersion( $version, $this->premiumDirectory );
		}
	}

	/**
	 * Sets the version number in JavaScript files via the Grunt command.
	 *
	 * @param string $version			The version to set the JavaScript to.
	 * @param string $targetDirectory	The target directory in which to execute the command.
	 *
	 * @return void
	 */
	protected function setGruntVersion( $version, $targetDirectory ) {
		$this->executeCommand( sprintf( 'grunt set-version --new-version=%s', $version ), $targetDirectory );
		$this->executeCommand( 'grunt update-version', $targetDirectory );
	}

	/**
	 * Creates the zip file.
	 *
	 * @param string $fileName The file name to give to the zip file.
	 *
	 * @return void
	 */
	protected function createZip( $fileName ) {
		$this->CLIPresenter->note( 'Building the zip' );
		$this->durationTracker->start( 'createZip' );

		$this->setVersion( $fileName );

		$command = $this->executeCommand( 'grunt artifact', $this->buildDirectory );

		if ( $command->isSuccessful() && $this->isPremium() ) {
			$this->CLIPresenter->note( 'Creating artifact in Premium' );

			// Node-sass seems to have some issues, so we need to rebuild it.
			$this->executeCommand( 'npm rebuild node-sass', $this->premiumDirectory );
			$this->executeCommand( 'grunt artifact', $this->premiumDirectory );
		}

		$this->moveZip( $fileName );
		$event = $this->durationTracker->stop( 'createZip' );

		$this->successMessage( 'Zip complete.', $event );
	}

	/**
	 * Moves the zip file to the proper directory.
	 *
	 * @param string $fileName The file name to give to the moved file.
	 *
	 * @return void
	 */
	protected function moveZip( $fileName ) {
		if ( $this->filesystem->exists( $this->zipsDirectory ) === false ) {
			$this->filesystem->mkdir( $this->zipsDirectory );
		}

		$this->filesystem->rename(
			$this->buildDirectory . '/artifact.zip',
			$this->zipsDirectory . sprintf( '/%s.zip', $fileName )
		);
	}

	/**
	 * Lists the repository options within the console.
	 *
	 * @return mixed The user's answer.
	 */
	protected function listMenuOptions() {
		return $this->CLIPresenter->choice( 'What repository do you want to create the zip for?', $this->menu->output(
			function( $item ) { return $item->buildable === true; }
		) );
	}

	/**
	 * Asks the user what branch they want to set before building the zip.
	 *
	 * @param string $repository The repository to build.
	 * @param bool 	 $required	 Whether or not choosing a branch is required.
	 *
	 * @throws \RuntimeException
	 *
	 * @return string The selected branch.
	 */
	protected function chooseBranch( $repository, $required = false ) {
		return $this->CLIPresenter->ask(
			sprintf( '[%s] What branch do you want to build?', $repository ),
			null,
			function( $item ) use ( $required ) {
				return $this->checkIfBranchIsRequired( $item, $required );
			}
		);
	}

	/**
	 * Determines whether a branch name is required or not.
	 *
	 * If so, it throws an RuntimeException. Otherwise, it returns a default value.
	 *
	 * @param mixed $branch     The branch to validate.
	 * @param bool  $isRequired Whether or not it is required.
	 * @param mixed $default    The default value to return.
	 *
	 * @return mixed The item itself if it passes the validation or the default if empty.
	 */
	protected function checkIfBranchIsRequired( $branch, $isRequired = false, $default = '' ) {
		if ( empty( $branch ) && $isRequired === true ) {
			throw new \RuntimeException( 'You must name the branch you want to build.' );
		}

		if ( empty( $branch ) ) {
			return $default;
		}

		return $branch;
	}

	/**
	 * Asks the user what name they want to give the zip and ensures it's valid name.
	 *
	 * @return string The zip's name.
	 */
	protected function chooseName() {
		$zipName = $this->CLIPresenter->ask( 'How do you want to name the file?',
			null,
			function( $chosen_name ) {
				if ( empty( $chosen_name ) ) {
					throw new \RuntimeException( 'Zip filename cannot be empty' );
				}

				if ( $this->filesystem->exists( $this->zipsDirectory . '/' . $chosen_name . '.zip' ) ) {
					throw new FileAlreadyExistsException( $chosen_name );
				}

			return $chosen_name;
		} );

		return str_replace( ' ', '-', trim( $zipName ) );
	}

	/**
	 * Displays an overview of the selected branches and asks for user confirmation before building.
	 *
	 * @return bool Whether or not the user confirmed their choices.
	 */
	protected function confirmChoices() {
		$branches = array_merge( [ $this->pluginBranch ], $this->dependencyBranches );

		$this->CLIPresenter->text( 'The following selected branches will be combined in the zip: ' );
		$this->CLIPresenter->listing( $branches );

		return $this->CLIPresenter->confirm( 'Are you sure you want to build the zip?' );
	}

	/**
	 * Clones a repository with a specific branch to the set directory.
	 *
	 * @param string $repository The repository to clone.
	 * @param string $branch 	 The branch to clone.
	 * @param string $directory  The directory to clone into.
	 *
	 * @return void
	 */
	protected function cloneRepository( $repository, $branch, $directory = './tmp' ) {
		$this->CLIPresenter->note( sprintf( 'Cloning branch %s from %s', $branch, $repository ) );

		$this->durationTracker->start( 'cloneRepository' );
		$this->executeCommand(
			sprintf(
				'git clone -b %s --single-branch https://github.com/Yoast/%s %s',
				$branch,
				$repository,
				$directory
			)
		);

		$event = $this->durationTracker->stop( 'cloneRepository' );

		$this->successMessage( 'Cloning complete.', $event );
	}

	/**
	 * Generates a success message and optionally displays the duration.
	 *
	 * @param string              $message The success message.
	 * @param StopwatchEvent|null $event   The event to gather the execution time from.
	 *
	 * @return void
	 */
	protected function successMessage( $message, StopwatchEvent $event = null ) {
		if ( ! is_string( $message ) || $message === '' ) {
			throw new \RuntimeException( 'Message must be a string and cannot be empty.' );
		}

		$successMessage = $message;

		if ( ! empty( $event ) ) {
			$successMessage .= sprintf( ' (Took %s seconds)', $this->durationTracker->getDuration( $event ) );
		}

		return $this->CLIPresenter->success( $successMessage );
	}

	/**
	 * Clears the output buffer.
	 *
	 * @param OutputInterface $output The output to clear.
	 *
	 * @return void
	 */
	protected function clearOutput( OutputInterface $output ) {
		$output->write( sprintf( "\033\143" ) );
	}

	/**
	 * Removes the passed dependency.
	 *
	 * @param string $dependency The dependency to delete.
	 * @param string $directory	 The directory to execute the command in.
	 *
	 * @return void
	 */
	protected function removeDependency( $dependency, $directory ) {
		// First we need remove the latest version because otherwise newer versions won't install.
		$this->executeCommand( sprintf( 'yarn remove %s', $dependency ), $directory );
	}
}
