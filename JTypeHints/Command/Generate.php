<?php
/**
 * @package   JTypeHints
 * @copyright Copyright (c) 2017-2022 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Akeeba\JTypeHints\Command;

use Akeeba\JTypeHints\Engine\Classmap;
use Akeeba\JTypeHints\Engine\Generator;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class Generate
{
	public function __invoke($folder, $forVersion, $forSite, $noOverwrite, OutputInterface $output)
	{
		if (empty($folder))
		{
			$folder = __DIR__ . '/../../generated_hints';
		}

		$parser = null;

		if (!empty($forVersion))
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
		$generator
			->setGeneratedFor(empty($forSite) ? "version $forVersion" : $forSite)
			->setOverwrite(!$noOverwrite)
			->generate($folder);
	}
}