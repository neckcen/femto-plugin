PDF plugin for Femto
====================

A plugin to render pages as PDF files using 
[dompdf](https://github.com/dompdf/dompdf).

See demo page for usage.

Requirements
------------

* [PHP GD extension](http://php.net/manual/en/intro.image.php)
* [PHP DOM extension](http://php.net/manual/en/intro.dom.php)
* [PHP iconv extension](http://php.net/manual/en/intro.iconv.php)
* [PHP mbstring extension](http://php.net/manual/en/intro.mbstring.php)

Installation
------------
Copy `pdf.php` and the `pdf` folder to `femto/plugins` then add _PDF_ to the 
list of enabled plugins in `index.php`:

    $config['plugin_enabled'] = 'PDF';

