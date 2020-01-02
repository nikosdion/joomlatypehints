<?php
/**
 * @package   JTypeHints
 * @copyright Copyright (c) 2017-2020 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Akeeba\JTypeHints\Command;

use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class Table extends Collect
{
	public function __invoke($format = 'markdown', OutputInterface $output)
	{
		$filePath = __DIR__ . '/../../classmapstats.json';
		$stats    = $this->loadClassmapStats($filePath);

		if (empty($stats))
		{
			$output->writeln("Please remember to run the collect command first");

			return;
		}

		switch ($format)
		{
			case 'markdown':
				$out = $this->renderAsMarkdown($stats);

				$output->writeln($out, OutputInterface::OUTPUT_RAW);
				break;

			case 'page':
				$content = file_get_contents(__DIR__ . '/../template.md');

				$out     = $this->renderAsMarkdown($stats);
				$matrix  = implode("\n", $out);
				$content = str_replace('[THE_MATRIX]', $matrix, $content);

				$output->writeln($content, OutputInterface::OUTPUT_RAW);
				break;

			default:
				$output->writeln("Please set a valid output format with the --format option");
				break;
		}
	}

	/**
	 * @param $stats
	 *
	 * @return array
	 */
	private function renderAsMarkdown($stats): array
	{
		$out   = [];
		$out[] = '| Old class | New Class | Deprecated since | Obsolete Since |';
		$out[] = '| --- | --- | --- | --- |';

		foreach ($stats as $oldClass => $info)
		{
			$out[] = "| $oldClass | {$info['new']} | {$info['min']} | {$info['max']} |";
		}

		return $out;
	}


}