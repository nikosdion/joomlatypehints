<?php
/**
 * @package   JTypeHints
 * @copyright Copyright (c) 2017-2023 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;

/**
 * Rector 0.14 configuration for converting legacy Joomla! classes to namespaced ones, compatible with Joomla! 3.4
 */
return static function (RectorConfig $rectorConfig): void {
	$rectorConfig->ruleWithConfiguration(
		RenameClassRector::class,
		[

'JRegistry' => 'Joomla\Registry\Registry',
			'JRegistryFormat' => 'Joomla\Registry\AbstractRegistryFormat',
			'JRegistryFormatINI' => 'Joomla\Registry\Format\Ini',
			'JRegistryFormatJSON' => 'Joomla\Registry\Format\Json',
			'JRegistryFormatPHP' => 'Joomla\Registry\Format\Php',
			'JRegistryFormatXML' => 'Joomla\Registry\Format\Xml',
			'JStringInflector' => 'Joomla\String\Inflector',
			'JStringNormalise' => 'Joomla\String\Normalise',

		]
	);
};
