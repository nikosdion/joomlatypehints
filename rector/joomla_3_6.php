<?php
/**
 * @package   JTypeHints
 * @copyright Copyright (c) 2017-2022 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * Rector 0.8/0.9 configuration for converting legacy Joomla! classes to namespaced ones, compatible with Joomla! 3.6
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->public()
        ->autoconfigure();

	$services->set(RenameClassRector::class)
		->call('configure', [
			[
				RenameClassRector::OLD_TO_NEW_CLASSES => [
'JRegistry' => 'Joomla\Registry\Registry',
					'JRegistryFormat' => 'Joomla\Registry\AbstractRegistryFormat',
					'JRegistryFormatINI' => 'Joomla\Registry\Format\Ini',
					'JRegistryFormatJSON' => 'Joomla\Registry\Format\Json',
					'JRegistryFormatPHP' => 'Joomla\Registry\Format\Php',
					'JRegistryFormatXML' => 'Joomla\Registry\Format\Xml',
					'JStringInflector' => 'Joomla\String\Inflector',
					'JStringNormalise' => 'Joomla\String\Normalise',
					'JRegistryFormatIni' => 'Joomla\Registry\Format\Ini',
					'JRegistryFormatJson' => 'Joomla\Registry\Format\Json',
					'JRegistryFormatPhp' => 'Joomla\Registry\Format\Php',
					'JRegistryFormatXml' => 'Joomla\Registry\Format\Xml',
					'JApplicationWebClient' => 'Joomla\Application\Web\WebClient',
				],
			]
		]);
};