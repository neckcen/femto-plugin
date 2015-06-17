Directory plugin for Femto
==========================

A plugin to display the content of a directory.

Installation
------------
Copy `directory.php` to `femto/plugins` then add _Directory_ to the list of enabled plugins
in `index.php`:

    $config['plugin_enabled'] = 'Directory';

or

    $config['plugin_enabled'] = 'Other_Plugin,Directory';

Usage
-----
Add `%directory:path/%` anywhere in the page. Sort method and order can be
specified optionally. Path can be relative.

    %directory:/,alpha,asc%
    %directory:./,alpha,desc%
    %directory:../%
