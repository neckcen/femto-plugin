Gallery plugin for Femto
========================

A plugin to create neat gap-less galleries.

Requirements
------------

* [Image plugin](https://github.com/neckcen/femto-plugin/tree/master/image)

Installation
------------
Copy `gallery.php` to `femto/plugins` then add _Gallery_ to the list of enabled
plugins in `index.php`:

    $config['plugin_enabled'] = 'Image,Gallery';

It is recommended to add the following styles to your theme as well:

    .gallery {
        margin:0 auto;
        max-width:100%;
    }
    .gallery ul {
        list-style-type:none;
        margin:0;
        padding:0;
    }
    .gallery li, .gallery a, .gallery img {
        display:inline-block;
        margin:0;
        padding:0;
        text-decoration:none;
    }
    .gallery img {
        height:auto;
        width:100%;
    }

Usage
-----
Add `%gallery:width:height%` anywhere in the page. Width is exact total width of
the gallery. Height is the ideal height of a single row and may vary. Both are
expressed in pixels.

    %gallery:600x200%
