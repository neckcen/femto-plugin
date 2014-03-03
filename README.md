PHP plugin for Femto
====================

A plugin to associate a PHP file with a page and run it before it is rendered.

See demo page for usage.

Requirements
------------

* [Femto](https://github.com/neckcen/femto)

Installation
------------
Copy `php.php` to `femto/plugins` then add _PHP_ to the list of enabled plugins
in `index.php`:

    $config['plugin_enabled'] = 'PHP';

or

    $config['plugin_enabled'] = 'Other_Plugin,PHP';

A note on cache
---------------
By default Femto caches rendered pages, consequently the PHP script associated
with a page will only run once. If you need it to run every time the page is
displayed, disable page-level caching in the header:

    /*
    Title: My Page
    PHP: my_script.php
    No-Cache: page
    */
