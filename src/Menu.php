<?php namespace Yoast;

use Symfony\Component\Yaml\Yaml;

/**
 * Class Menu.
 */
class Menu {

	/**
	 * @var array
	 */
	private $options = [];

	/**
	 * Menu constructor.
	 *
	 * @param string $configurationFile The configuration file to parse.
	 */
	public function __construct( $configurationFile ) {
		$this->options = $this->parseConfiguration( $configurationFile );
	}

	/**
	 * Parses the passed configuration file and ensures sane defaults are set.
	 *
	 * @param string $configuration The name of the configuration file.
	 *
	 * @return array Array containing the menu options.
	 */
	protected function parseConfiguration( $configuration ) {
		$parsedFile = Yaml::parseFile( $configuration );

		return array_map( function( $name, $item ) {
			if ( ! array_key_exists( 'buildable', $item ) ) {
				$item['buildable'] = false;
			}

			if ( ! array_key_exists( 'mainfile', $item ) ) {
				$item['mainfile'] = '';
			}

			return new MenuOption( $item['option'], $name, $item['endpoint'], $item['mainfile'], $item['dependencies'], $item['buildable'] );
		}, array_keys( $parsedFile ), $parsedFile );
	}

	/**
	 * Outputs the created menu.
	 *
	 * @param callable|null $filter Optional filter that can be applied to the menu items prior to outputting the menus.
	 *
	 * @return array The menu.
	 */
	public function output( callable $filter = null ) {
		$options = $this->options;

		if ( ! is_null( $filter ) ) {
			$options = array_filter( $options, $filter );
		}

		return array_column( $options, 'label', 'option' );
	}

	/**
	 * Finds a particular MenuOption based on the passed option key.
	 *
	 * @param string $option The option key to search with.
	 *
	 * @return MenuOption The found MenuOption.
	 */
	public function findByOption( $option ) {
		$item = array_filter( $this->options, function( $item ) use ( $option ) {
			return $item->option === $option;
		} );

		return array_shift( $item );
	}
}
