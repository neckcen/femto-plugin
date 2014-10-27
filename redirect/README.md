Redirect plugin for Femto
===========================

A plugin to redirect a page to somewhere else.

Installation
------------
Copy `redirect.php` to `femto/plugins` then add <em>Redirect</em> to the
list of enabled plugins in `index.php`:

    $config['plugin_enabled'] = 'Redirect';

or

    $config['plugin_enabled'] = 'Other_Plugin,Redirect';

Usage
-----
Set the `redirect` header to the target url.

    /*
    Redirect: http://domain.tld/
    */

The `redirect-permanent` header can be used to ask browsers to cache the
redirection (defaults to no).

    /*
    Redirect: http://domain.tld/
    Redirect-permanent: yes
    */

`%base_url%` can be used in the target.

    /*
    Redirect: %base_url%/path
    */

