<?php
/**
 * Created by PhpStorm.
 * User: sledg
 * Date: 8/2/2017
 * Time: 12:44 PM
 */

namespace Akeeba\JTypeHints;


use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Classmap
{
	public static function getForVersion($version, $force = false)
	{
		$targetPath = __DIR__ . '/../tmp';

		self::ensurePathExists($targetPath);

		$filePath = self::downloadJoomlaVersion($version, $targetPath, $force);
		$content  = self::getClassmapFile($filePath);

		return Parser::fromContent($content);
	}

	private static function ensurePathExists($targetPath)
	{
		if (!is_dir($targetPath))
		{
			if (mkdir($targetPath, 0755) === false)
			{
				throw new \RuntimeException("Cannot create folder $targetPath");
			}
		}
	}

	private static function downloadJoomlaVersion($version, $targetPath, $force = false)
	{
		// Calculate filepath of the downloaded ZIP file
		$filePath = rtrim($targetPath, "\\/") . "/Joomla_$version-Stable-Full_Package.zip";

		// Does the file already exist?
		if (!$force && is_file($filePath))
		{
			return $filePath;
		}

		// Download and save
		$url    = "https://github.com/joomla/joomla-cms/releases/download/$version/Joomla_$version-Stable-Full_Package.zip";
		$client = new Client();
		$res    = $client->request('GET', $url, [
			RequestOptions::ALLOW_REDIRECTS => true,
		]);

		file_put_contents($filePath, $res->getBody());

		return $filePath;
	}

	private static function getClassmapFile($zipFilename)
	{
		$zip = new \ZipArchive();

		if ($zip->open($zipFilename) !== true)
		{
			throw new \RuntimeException("Cannot open ZIP file $zipFilename");
		}

		$pathInZip = 'libraries/classmap.php';
		$fileData  = $zip->getFromName($pathInZip);

		if ($fileData === false)
		{
			throw new \RuntimeException("Cannot find file $pathInZip in ZIP file $zipFilename");
		}

		return $fileData;
	}
}