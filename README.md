Image plugin for Femto
======================

A plugin to make working with images easier. Allows you to display images from
the content folder and create thumbnails for them.

See demo page for usage.

Requirements
------------

* [Femto](https://github.com/neckcen/femto)
* [PHP GD extension](http://php.net/manual/en/intro.image.php)
* [PHP EXIF extension](http://php.net/manual/en/intro.exif.php)

Installation
------------
Copy `image.php` to `femto/plugins` then add _Image_ to the list of enabled
plugins in `index.php`:

    $config['plugin_enabled'] = 'Image';

or

    $config['plugin_enabled'] = 'Other_Plugin,Image';

It is recommended to add the following styles to your theme as well:

    figure {
        border:1px solid #ddd;
        max-width:100%;
        padding:2px;
	    -moz-box-sizing: border-box;
	    -webkit-box-sizing: border-box;
	    box-sizing: border-box;
    }
    .left {
        float:left;
        margin:.4em .4em .4em 0;
    }
    .right {
        float:right;
        margin:.4em 0 .4em .4em;
    }
    .center {
        margin:.4em auto;
    }
