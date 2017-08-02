<?php
/**
 * @package   JTypeHints
 * @copyright Copyright (c) 2017 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

use Akeeba\JTypeHints\Engine\Classmap;
use Akeeba\JTypeHints\Engine\Generator;
use Symfony\Component\Console\Output\OutputInterface;

// Load Composer's autoloader
$loader = require_once __DIR__ . '/vendor/autoload.php';

// Load the version.php file
require_once 'version.php';

$app = new Silly\Application('Joomla! TypeHints Helper', JTHH_VERSION);

$app->command('generate folder [--for-version=] [--for-site=]', function ($folder, $forVersion, $forSite, OutputInterface $output) {
	if (empty($folder))
	{
		throw new RuntimeException("You must provide an output folder");
	}

	$parser = null;

	if (!empty($forVersion)
		&& version_compare($forVersion, '3.3.0', 'ge')
		&& version_compare($forVersion, '4.0.0', 'lt'))
	{
		$output->writeln("Generating typehints for Joomla! $forVersion");
		$forSite = '';

		$parser = Classmap::getForVersion($forVersion);
	}
	elseif (!empty($forSite) && is_dir($forSite))
	{
		$output->writeln("Generating typehints for Joomla! installation in $forSite");
		$forVersion = '';

		$parser = Classmap::getForPath($forSite);
	}

	if (empty($parser))
	{
		throw new RuntimeException("A valid Joomla! version or site path is required to run 'generate'");
	}

	$output->writeln("Typehint classes will be written to $folder");

	$generator = new Generator($parser);
	$generator->setGeneratedFor(empty($forSite) ? "version $forVersion" : $forSite);
	$generator->generate($folder);
})->descriptions(
	'Generates type hints for a specific Joomla! version or installed site',
	[
		'folder' => 'Where do you want the typehint class files to be stored',
		'--for-version' => 'Joomla! version number for which to generate the typehints',
		'--for-site' => 'Path to a Joomla! installation for which to generate the typehints'
	]
);
//$app->setDefaultCommand('generate');

$app->run();