# TypeHint Helper for Joomla!

Generate code type hints for deprecated core Joomla! API classes

**Did you find this useful? [Please buy me coffee or lunch :)](https://paypal.me/nicholasakeeba)** Include your Twitter handle for a public thank-you!

## What does it do?

It lets your IDE provide code completion for the old core Joomla! API classes which have been deprecated and replaced with their
namespaced counterparts.

A picture is worth a thousand words. Here's Joomla! 3.8 staging branch on phpStorm.

**Before**

![Before applying any typehints. Boo!](https://raw.githubusercontent.com/nikosdion/joomlatypehints/master/docs/before.png)

**After**

![Code hinting, type checks and deprecated warnings? Check!](https://raw.githubusercontent.com/nikosdion/joomlatypehints/master/docs/after_01.png)

![Code completion and method hints? Check!](https://raw.githubusercontent.com/nikosdion/joomlatypehints/master/docs/after_02.png)

## Usage

### Installation and first use

Clone this repository and initialize Composer dependencies in it

```bash
git clone https://github.com/nikosdion/joomlatypehints.git
cd joomlatypehints
composer install
```

You can create typehints against either a published version of Joomla! or a Joomla! installation on your computer. The latter is
useful for core development against the staging branch.

Against a published Joomla! version, e.g. 3.7.4
```bash
php typehint.php --for-version=3.7.4
```

Ninja developer tip: The `--for-version` argument also accepts any published GitHub branch name for the joomla/joomla-cms repository. For example, if you want to generate typehints for the current staging branch:

```bash
php typehint.php --for-version=staging
``` 

Against a Joomla! installation, e.g. /var/www/joomla-cms
```bash
php typehint.php --for-site=/var/www/joomla-cms
```

By default the typehint classes are output in the `generated_hints` folder. You can change that by passing a folder name to the
command (the folder must already exist). For example:
```bash
php typehint.php --for-site=/var/www/joomla-cms /var/www/joomla-cms/typehints
```

Invoke the application without any parameters to get help. It's self-documenting!

### Using the typehints with phpStorm

* Go to File, Settings.
* From the left hand tree select Language & Frameworks, PHP.
* Click on the Include Path tab.
* Click the [+] button to the right hand of the include path list.
* Select _Specify Other..._ and select the generated class hints folder.

## How does it do it?

It reads Joomla's libraries/classmap.php file and generates fake class files so that the deprecated class extends from the new,
namespaced class. **You have to include these files in your IDE's class search path for code completion to work**. 

For example, `JRegistry` stopped existing as a standalone class in Joomla! 3.3.0. It was replaced by `\Joomla\Registry\Registry`.
Any code which was typehinted to JRegistry stopped showing code hints to developers since 3.3.0.

This utility creates a fake class definition file for this class in the form
```php
/**
 * @deprecated 4.0
 */
class JRegistry extends \Joomla\Registry\Registry {}
``` 

This lets your IDE know that even if you have used `JRegistry` in your DocBlocks / PHP 7.x type hints it should provide code
completion for the new `\Joomla\Registry\Registry` class. At the same time your IDE will mark the old `JRegistry` class as
deprecated (if it supports such a feature) so you can eventually refactor it to the new class name.

## Joomla! trademark disclaimer

This project is not affiliated with or endorsed by the Joomla! Project. It is not supported or warranted by the Joomla! Project or Open Source Matters. Open Source Matters is the trademark holder of the Joomla! name and logo in the United States and other countries. The Joomla! name is used by this project according to the [fair use](https://en.wikipedia.org/wiki/Fair_use) doctrine.