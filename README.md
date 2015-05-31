Plugins for Femto
=================

Femto supports plugins to extend its functions.

Available plugins
-----------------

- [Gallery](https://github.com/neckcen/femto-plugin/tree/master/gallery) -
Gap-less image galleries.
- [Image](https://github.com/neckcen/femto-plugin/tree/master/image) - Link to
and display images from the content folder.
- [Page Extra](https://github.com/neckcen/femto-plugin/tree/master/page_extra) -
Extra information and sorting option for pages.
- [PDF](https://github.com/neckcen/femto-plugin/tree/master/pdf) - Render pages
as PDF files.
- [PHP](https://github.com/neckcen/femto-plugin/tree/master/php) - Associate PHP
scripts with your pages.
- [Redirect](https://github.com/neckcen/femto-plugin/tree/master/redirect) - 
Redirect a page to somewhere else.
- [TOC](https://github.com/neckcen/femto-plugin/tree/master/toc) - Display a
table of content.

Create your own
---------------

Plugins are essentially a class in a php file of the same name. Class name is
case sensitive, file name will always be lower case. Plugins need to be in the
`femto\plugin` namespace. This example plugin would go in `my_plugin.php`:

    namespace femto\plugin;

    class My_Plugin {
        //...
    }

The plugin class can define functions with specific names -_hooks_- to be called
when the corresponding event happens. Below is a list of available hooks, most
parameters are passed by reference:

### __construct($config)
Let you initialise your plugin with the given configuration.

### request_url(&$url)
Called when the URL has been cleaned and is about to be dispatched.

### request_not_found(&$current_page)
Called if the request didn't match anything.

### request_complete(&$current_page)
Called when the request has been completed, even if it did not match anything,
before the page is inserted in the template.

### page_parse_header_before(&$headers)
Called before parsing a page's header. It is possible add custom headers at this
point:

    public function page_parse_header_before(&$headers) {
        $headers['name'] = 'default value';
    }

This hook is not called if the page is served from cache.

### page_parse_header_after(&$page)
Called after parsing a page's header but before parsing its content. This hook 
is not called if the page is served from cache.

### page_parse_content_before(&$page)
Called before parsing a page's content. This hook is not called if the page is 
served from cache. Page content cache can be disabled with the `no-cache` flag.

### page_parse_content_after(&$page)
Called after parsing a page's content. This hook is not called if the page is 
served from cache. Page content cache can be disabled with the `no-cache` flag.

### render_before(&$twig_vars, &$twig, &$template)
Called before rendering the page with the appropriate template. This hook is not
called if the `no-theme` flag is set.

### render_after(&$output, $with_theme)
Called just before displaying the page with the final output.

### directory_complete(&$directory)
Called when a directory listing is completed. This hook is not called if the
directory's information is taken from the cache.

### directory_sort(&$directory, &$sort)
Called when a directory is being sorted. Note that directories should always be
sorted in descending order, Femto will reverse it if needed.

### url($url)
Called when the plugin's url is accessed (e.g.
`http://example.com/plugin/my_plugin/foo/bar`). Only the relevant part of the
url is passed as argument (e.g. `foo/bar`).
