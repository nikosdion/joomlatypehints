<?php
/**
 * @package   JTypeHints
 * @copyright Copyright (c) 2017-2023 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Akeeba\JTypeHints\Command;

use Akeeba\JTypeHints\Engine\Classmap;
use Akeeba\JTypeHints\Engine\Generator;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class Collect
{
	public function __invoke($forVersion, OutputInterface $output)
	{
		$parser = null;

		if (!empty($forVersion))
		{
			$output->writeln("Collecting classmap for Joomla! $forVersion");

			$parser = Classmap::getForVersion($forVersion);
		}

		if (empty($parser))
		{
			throw new RuntimeException("A valid Joomla! version run 'collect'");
		}

		$output->writeln("Adding information to classmapstats.json");

		$filePath = __DIR__ . '/../../classmapstats.json';

		$classes = [];
		$map = $parser->getMap();
		$versions = $parser->getMaxVersionMap();

		foreach ($map as $oldClass => $newClass)
		{
			$classes[$oldClass] = [
				'min' => $forVersion,
				'max' => isset($versions[$oldClass]) ? $versions[$oldClass] : '4.0',
				'new' => $newClass,
			];
		}

		$stats = $this->loadClassmapStats($filePath);

		foreach ($classes as $oldClass => $info)
		{
			if (!isset($stats[$oldClass]))
			{
				$stats[$oldClass] = $info;

				continue;
			}

			$oldInfo = $stats[$oldClass];

			$minThis = ($info['min'] == 'staging') ? '999.999.999' : $info['min'];
			$minOld  = ($oldInfo['min'] == 'staging') ? '999.999.999' : $oldInfo['min'];

			if (version_compare($minThis, $minOld, 'lt'))
			{
				$oldInfo['min'] = $minThis;
			}

			$maxThis = $info['max'];
			$maxOld  = $info['max'];

			if (version_compare($maxThis, $maxOld, 'gt'))
			{
				$oldInfo['max'] = $maxThis;
			}

			$stats[$oldClass] = $oldInfo;
		}

		$this->saveClassmapStats($filePath, $stats);
	}

	protected function loadClassmapStats(string $filePath): array
	{
		if (!is_file($filePath))
		{
			return [];
		}

		$json = @file_get_contents($filePath);

		if (empty($json))
		{
			return [];
		}

		$array = @json_decode($json, true);

		if (empty($array))
		{
			return [];
		}

		return $array;
	}

	private function saveClassmapStats(string $filePath, array $stats): bool
	{
		return @file_put_contents($filePath, json_encode($stats));
	}
}