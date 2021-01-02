<?php
/**
 * @package   JTypeHints
 * @copyright Copyright (c) 2017-2021 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Akeeba\JTypeHints\Engine;

use GuzzleHttp\Client;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\RequestOptions;

class Classmap
{
	/**
	 * Get the classmap parser object for a given Joomla! version. The Joomla! ZIP file for that version will be downloaded if
	 * not present.
	 *
	 * @param   string  $version  The Joomla! version you want to fetch the classmap for.
	 * @param   bool    $force    Optional. Force the download of the Joomla! ZIP file, even if it is already present.
	 *
	 * @return  Parser
	 */
	public static function getForVersion(string $version, $force = false): Parser
	{
		$targetPath = sys_get_temp_dir();

		$filePath = self::downloadJoomlaVersion($version, $targetPath, $force);
		$content  = self::getClassmapFile($filePath);

		return Parser::fromContent($content);
	}

	/**
	 * Get the classmap parser object for a given Joomla! installation.
	 *
	 * @param   string  $path  The root path of a Joomla! installation on your computer.
	 *
	 * @return  Parser
	 */
	public static function getForPath(string $path): Parser
	{
		$filePath = $path . '/libraries/classmap.php';
		$content  = file_get_contents($filePath);

		if ($content === false)
		{
			throw new \RuntimeException("Cannot find classmap file $filePath  Is $path a Joomla! 3.x installation?");
		}

		return Parser::fromContent($content);

	}

	/**
	 * Download the ZIP file for a given Joomla! version if it's not already present.
	 *
	 * @param   string       $version     The Joomla! version to download
	 * @param   string|null  $targetPath  Optional. The path where the ZIP files are stored. Default: system temp-directory.
	 * @param   bool         $force       Optional. Set true to force download even if the file is present.
	 *
	 * @return  string  The full path to the ZIP file.
	 */
	private static function downloadJoomlaVersion(string $version, string $targetPath = null, $force = false): string
	{
		if (empty($targetPath))
		{
			$targetPath = sys_get_temp_dir();
		}

		// Calculate filepath of the downloaded ZIP file
		$filePath = rtrim($targetPath, "\\/") . "/Joomla_$version-Stable-Full_Package.zip";

		// Does the file already exist?
		if (!$force && is_file($filePath))
		{
			return $filePath;
		}

		// URL for Joomla! 3.4.0 and later (using GitHub releases)
		$url = "https://github.com/joomla/joomla-cms/releases/download/$version/Joomla_$version-Stable-Full_Package.zip";
		// Previous Joomla! versions used the now defunct JoomlaCode. We can still download the branch's archive as a ZIP though.
		$altUrl = "https://github.com/joomla/joomla-cms/archive/$version.zip";

		$client = new Client([
			// TODO Why the heck does this cause an error on Windows?!!
			//'verify' => __DIR__ . '/cacert.pem.txt',
			'verify' => false,
		]);
		$res    = $client->request('GET', $url, [
			RequestOptions::ALLOW_REDIRECTS => true,
			RequestOptions::HTTP_ERRORS     => false,
		]);

		$statusCode = $res->getStatusCode();

		if (($statusCode >= 400) && ($statusCode < 500))
		{
			$res = $client->request('GET', $altUrl, [
				RequestOptions::ALLOW_REDIRECTS => true,
				RequestOptions::HTTP_ERRORS     => true,
			]);
		}

		file_put_contents($filePath, $res->getBody());

		return $filePath;
	}

	/**
	 * Get the contents of the Joomla! classmap.php file given a Joomla! installation ZIP file
	 *
	 * @param   string  $zipFilename  The full path to the Joomla! installation ZIP file
	 *
	 * @return  string  The contents of the classmap.php file
	 */
	private static function getClassmapFile(string $zipFilename): string
	{
		$zip = new \ZipArchive();

		$amIOpen = $zip->open($zipFilename);

		if ($amIOpen !== true)
		{
			throw new \RuntimeException("Cannot open ZIP file $zipFilename");
		}

		// GitHub ZIP files have a bloody folder prepended to them. I'll have to find it.
		$foundIndex = false;
		$prefix     = '';

		for ($i = 0; $i < $zip->numFiles; $i++)
		{
			$name = $zip->getNameIndex($i);

			if ($name == 'index.php')
			{
				$foundIndex = true;

				break;
			}
		}

		if (!$foundIndex)
		{
			$name   = $zip->getNameIndex(1);
			$prefix = dirname($name) . '/';
		}

		$pathInZip = $prefix . 'libraries/classmap.php';
		$fileData  = $zip->getFromName($pathInZip);

		if ($fileData === false)
		{
			throw new \RuntimeException("Cannot find file $pathInZip in ZIP file $zipFilename");
		}

		$zip->close();

		return $fileData;
	}
}