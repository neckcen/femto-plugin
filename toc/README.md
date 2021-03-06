TOC plugin for Femto
====================

A plugin to display a table of content.

Installation
------------
Copy `toc.php` to `femto/plugins` then add _TOC_ to the list of enabled plugins
in `index.php`:

    $config['plugin_enabled'] = 'TOC';

or

    $config['plugin_enabled'] = 'Other_Plugin,TOC';

It is recommended to add the following styles to your theme as well:

    .toc {
        border:1px solid #ddd;
        float:right;
        font-size:.9em;
        margin:.5em 0 .5em .5em;
        padding:.3em .5em;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
    }
    .toc ol {
        margin:0 0 0 .5em;
    }
    .toc > ol {
        margin:0;
    }
    .toc ol li, .toc ol ol li, .toc ol ol ol li, .toc ol ol ol ol li {
        list-style-type:none;
        margin:0;
    }

Usage
-----
Add `%TOC%` anywhere in the page. Minimum and maximum title levels can be 
specified optionally.

    %TOC:3%
    %TOC:2,4%
