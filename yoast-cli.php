#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Yoast\Errors\MissingEnvironmentFileException;

$fileSystem = new \Symfony\Component\Filesystem\Filesystem();

try {
	if ( ! $fileSystem->exists( '.env' ) ) {
		throw new MissingEnvironmentFileException();
	}
} catch ( Exception $exception ) {
    echo $exception->getMessage();
    exit();
}


$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// Create a default menu.
$menu = new \Yoast\Menu( __DIR__ . '/configs/plugins.yaml' );

$application = new Application( 'Yoast CLI', '1.0' );
$application->add( new \Yoast\Commands\CreateChangelogCommand() );
$application->add( new \Yoast\Commands\CreateBetaCommand( $menu ) );
$application->run();
