Page Extra plugin for Femto
===========================

A plugin to add extra information to your pages.

Requirements
------------

* [Femto](https://github.com/neckcen/femto)

Installation
------------
Copy `page_extra.php` to `femto/plugins` then add <em>Page_Extra</em> to the
list of enabled plugins in `index.php`:

    $config['plugin_enabled'] = 'Page_Extra';

or

    $config['plugin_enabled'] = 'Other_Plugin,Page_Extra';

Two additional configuration keys can be set:

* `page_extra_date_format` - The format to use for PHP's
date](http://php.net/manual/en/function.date.php). Defaults to `jS M Y` (e.g.1st
Jan 2014).

* `page_extra_excerpt_length` - The length (in words) of the excerpt. Defaults
to 50.

Usage
-----

### Date
You can define a date in the header of your page. Anything that can be
understood by [PHP's strtotime](http://php.net/manual/en/function.strtotime.php)
will do.

    /*
    Title: My Page
    Date: 10 January 2014
    */

The date will be available in `$page['timestamp']`as timestamp as well as in
`$page['date_formatted']` as a formatted representation according to the
configuration.

The date can also be used as criteria to sort pages.

    {% for page in directory('/', 'date') %}

### Author
You can define an author in the header of your page.

    /*
    Title: My Page
    Author: me and myself
    */

### Excerpt
An excerpt of the page will be created and available in `$page['excerpt']`.

### Order
You can define an order in the header of your page.

    /*
    Title: My Page
    Order: 1
    */

This can then be used to list pages.

    {% for page in directory('/', 'order') %}
