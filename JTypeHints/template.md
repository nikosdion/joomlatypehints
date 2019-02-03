# Obsolete Joomla! class matrix

[THE_MATRIX]

## Why do you need this matrix

Joomla! has started replacing its core classes with namespaced classes since version 3.3.0. For example `JRegistry` is replaced by `Joomla\Registry\Registry`.

While Joomla! offers backwards compatibility for old classes, by means of its autoloader (`JLoader`), this does have an expiration date. Developers of Joomla! extensions are supposed to replace instances of the old classes with the namespaced ones in their codes.

In an ideal world, our extensions would only support the latest version of Joomla!, allowing us to replace all of these classes. In reality, we need to support older versions of Joomla!. So, which classes are safe to replace and how longer can we defer replacing? This matrix answers exactly this question.

## How to use this matrix

The _Deprecated since_ column contains the version of Joomla! where the replacement class became available. You **must not** replace classes whose _Deprecated since_ is greater than your **minimum** supported Joomla! version.

The _Obsolete since_ column contains the version of Joomla! where the old class ceases to be valid. You **must** replace classes whose _Obsolete since_ is greater than your **maximum** supported Joomla! version.

If these two conditions conflict you must either raise your minimum supported Joomla! version number (strongly recommended) or lower your maximum supported Joomla! version number.

## Dot-zero minor versions only

To maintain a modicum developer sanity, we only collect information about namespaced classes in dot-zero minor releases i.e. 3.3.0, 3.4.0, 3.5.0 and so on. A keen observer will see that some classes were namespaced in patch releases, e.g. 3.4.4. These classes appear as being namespaced in the next dot-zero minor release. For example, classes namespaced in 3.4.4 appear on the matrix as being namespaced since 3.5.0.

## How was this page generated

This page is maintained automatically using the TypeHint Helper for Joomla!. Every time Joomla! releases a new version we run `php typehints.php collect <new version>` where `<new version>` is the released Joomla! version. Then we run `php typehint.php table --format=page` to update this page. The template of this page is inside this repository, in the `template.md` file.