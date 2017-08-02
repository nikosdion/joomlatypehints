<?php
/**
 * Created by PhpStorm.
 * User: sledg
 * Date: 8/2/2017
 * Time: 1:08 PM
 */

namespace Akeeba\JTypeHints;


use PhpParser\Error;
use PhpParser\ParserFactory;
use RuntimeException;

class Parser
{
	private $content = '';

	private $map = [];

	private $maxVersion = [];

	/**
	 * Create an instance from the content of a PHP file
	 *
	 * @param   string  $content
	 *
	 * @return  Parser
	 */
	public static function fromContent(string $content): self
	{
		return new self($content);
	}

	/**
	 * Create an instance from a file that holds a Joomla! classmap
	 *
	 * @param   string  $file
	 *
	 * @return  Parser
	 */
	public static function fromFile($file): self
	{
		$content = file_get_contents($file);

		return self::fromContent($content);
	}

	/**
	 * Public constructor
	 *
	 * Parser constructor.
	 *
	 * @param   string  $content  The file content to parse
	 */
	public function __construct($content)
	{
		$this->content = $content;

		$this->parse();
	}

	/**
	 * Get the raw PHP content to parse
	 *
	 * @return  string
	 */
	public function getContent(): string
	{
		return $this->content;
	}

	/**
	 * Set the raw PHP content to parse
	 *
	 * @param   string   $rawContent
	 *
	 * @return  Parser
	 */
	public function setContent($rawContent): self
	{
		$this->reset();
		$this->content = $rawContent;
		return $this->parse();
	}

	/**
	 * Reset the object
	 *
	 * @return  Parser
	 */
	public function reset(): self
	{
		$this->rawContent = '';
		$this->map        = [];
		$this->maxVersion = [];

		return $this;
	}

	/**
	 * Parses the raw PHP content
	 *
	 * @return  Parser
	 */
	public function parse(): self
	{
		$this->map        = [];
		$this->maxVersion = [];

		$content = str_replace("\r", "\n", $this->content);
		$lines   = explode("\n", $content);
		$parser  = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

		foreach ($lines as $line)
		{
			if (strip_tags($line, 'JLoader::registerAlias') === false)
			{
				continue;
			}

			try
			{
				$entry = $this->parseLine($line, $parser);
			}
			catch (Error $e)
			{
				// Parser error: skip to the next line
				continue;
			}
			catch (RuntimeException $e)
			{
				// Not a line we understand: skip to the next line
				continue;
			}

			if (empty($entry['alias']) || empty($entry['original']))
			{
				continue;
			}

			if (empty($entry['version']))
			{
				$entry['version'] = '4.0';
			}

			$this->map[$entry['alias']] = $entry['original'];
			$this->maxVersion[$entry['alias']] = $entry['version'];
		}

		return $this;
	}

	/**
	 * Gets the class map in the format old class => new namespaced class
	 *
	 * @return  array
	 */
	public function getMap(): array
	{
		return $this->map;
	}

	/**
	 * Returns the maximum Joomla! version an old class will be supported in. Format: class => version
	 *
	 * @return  array
	 */
	public function getMaxVersionMap(): array
	{
		return $this->maxVersion;
	}

	/**
	 * Returns a list of all old classes which are no longer supported in Joomla! version $version. These classes will have to be
	 * replaced with their namespaced counterparts.
	 *
	 * @param   string  $version  The Joomla! version to check
	 *
	 * @return  array  No longer supported core classes
	 */
	public function getUnsupportedFor($version): array
	{
		$ret  = [];

		foreach ($this->maxVersion as $class => $maxVersion)
		{
			if (version_compare($version, $maxVersion, 'ge'))
			{
				$ret[] = $class;
			}
		}

		return $ret;
	}

	private function parseLine(string $line, \PhpParser\Parser $parser): array
	{
		$xo = $parser->parse('<?php ' . $line);

		if ($xo[0]->getType() != 'Stmt_Expression')
		{
			throw new RuntimeException("Not a valid expression statement");
		}

		/** @var \PhpParser\Node\Expr\StaticCall $expr */
		$expr = $xo[0]->expr;

		if ($expr->getType() != 'Expr_StaticCall')
		{
			throw new RuntimeException("Not a valid static call line");
		}

		if (($expr->class->getFirst() != 'JLoader') || ($expr->name->name != 'registerAlias'))
		{
			throw new RuntimeException("Not a call to JLoader::registerAlias");
		}

		if ((count($expr->args) < 2) || (count($expr->args) > 3))
		{
			throw new RuntimeException("Unknown call format to JLoader::registerAlias");
		}

		$entry['alias']    = $expr->args[0]->value->value;
		$entry['original'] = $expr->args[1]->value->value;
		$entry['version']  = isset($expr->args[2]) ? $expr->args[2]->value->value : '4.0';

		return $entry;
	}
}