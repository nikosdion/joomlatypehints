<?php
/**
 * @package   JTypeHints
 * @copyright Copyright (c) 2017 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Akeeba\JTypeHints\Command;

use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class Rector extends Collect
{
	public function __invoke($folder = 'rector', OutputInterface $output)
	{
		$filePath = __DIR__ . '/../../classmapstats.json';
		$stats    = $this->loadClassmapStats($filePath);

		if (empty($stats))
		{
			$output->writeln("Please remember to run the collect command first");

			return;
		}

		$lastResult = [];

		for ($major = 3; $major <= 4; $major++)
		{
			for ($minor = 0; $minor <= 99; $minor++)
			{
				$version = "$major.$minor.0";
				$map     = $this->filterForVersion($stats, $version);

				if ($map == $lastResult)
				{
					continue;
				}

				$lastResult = $map;
				$fileName   = "joomla_{$major}_{$minor}.yaml";

				$yaml = <<< YAML
services:
  Rector\Rector\Class_\RenameClassRector:

YAML;

				foreach ($map as $old => $new)
				{
					$old  = ltrim($old, '\\');
					$new  = ltrim($new, '\\');
					$yaml .= "    $old: $new\n";
				}

				file_put_contents($folder. '/' . $fileName, $yaml);
			}
		}
	}

	private function filterForVersion(array $stats, $version)
	{
		$ret = [];

		foreach ($stats as $oldClass => $record)
		{
			if (version_compare($record['min'], $version, 'gt'))
			{
				continue;
			}

			$ret[$oldClass] = $record['new'];
		}

		return $ret;
	}
}