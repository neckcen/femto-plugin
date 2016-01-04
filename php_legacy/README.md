PHP legacy plugin for Femto
===========================

A plugin to associate a PHP file with a page and run it before it is rendered.

*Support for this plugin has been discontinued. Please use the
[PHP plugin](https://github.com/neckcen/femto-plugin/tree/master/php) instead.*

Installation
------------
Copy `php.php` to `femto/plugins` then add _PHP_legacy_ to the list of enabled
plugins in `index.php`:

    $config['plugin_enabled'] = 'PHP_legacy';

or

    $config['plugin_enabled'] = 'Other_Plugin,PHP_legacy';

Two additional configuration keys can be set:

* `php_legacy_form_validate` - Whether the plugin should validate forms and populate
them with submitted data. Enabled by default.

* `php_legacy_form_error_class` - Name of the class to add to incorrect fields.

For example, add in `index.php`:

    $config['php_legacy_form_validate'] = False;

Usage
-----
Set the `php` header to the name of the script you want to run. Path is relative
to the current page.

    /*
    Title: My Page
    PHP: my_script.php
    */

Form validation and persistence
-------------------------------
If enabled, the plugin will attempt to validate submitted forms based on their
HTML markup (e.g. a `type="email"` field will be checked to contain a valid
email address). Php script have access to `$forms` which will contain the status
of all forms, see demo for usage.

Since modern browsers validate forms before submission, the plugin is only 
intended as a server-side safeguard and does not provide the user with the
reason a field is incorrect.

The plugin will also populate forms with submitted data so that users do not
lose any information entered.

A note on cache
---------------
By default Femto caches rendered pages, consequently the PHP script associated
with a page will only run once. If you need it to run every time the page is
displayed, disable page-level caching in the header:

    /*
    Title: My Page
    PHP: my_script.php
    Flags: no-cache
    */
