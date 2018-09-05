<?php namespace Yoast\Writers;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Markdown
 */
class Markdown {

	/**
	 * @var string
	 */
	private $baseDir;

	/**
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * Markdown constructor.
	 *
	 * @param string $baseDir The base dir to use.
	 */
	public function __construct( $baseDir ) {
		$this->baseDir    = $baseDir;
		$this->filesystem = new Filesystem();
	}

	/**
	 * Checks if the base dir exists.
	 *
	 * @return bool Whether or not the base dir exists.
	 */
	protected function baseDirExists() {
		return $this->filesystem->exists( $this->baseDir );
	}

	/**
	 * Creates the base dir. Throws error if it doesn't succeed.
	 *
	 * @throws IOException
	 * @return void
	 */
	protected function createBaseDir() {
		try {
			$this->filesystem->mkdir( $this->baseDir );
		}
		catch ( IOExceptionInterface $e ) {
			throw new IOException( "Could not create base dir " . $e->getPath() );
		}
	}

	/**
	 * Writes the file's content to the destination.
	 *
	 * @param string $destination The destination file.
	 * @param string $content     The content of the file.
	 *
	 * @return void
	 */
	public function write( $destination, $content ) {
		if ( ! $this->baseDirExists() ) {
			$this->createBaseDir();
		}

		$this->filesystem->dumpFile( $destination, $content );
	}
}
