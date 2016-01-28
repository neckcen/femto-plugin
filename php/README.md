PHP plugin for Femto
====================

A plugin to allow PHP code within Femto pages.

**Note on security:** If your content folder is visible from the web (which 
should not be the case), then be aware that the PHP code within Femto pages 
will be displayed as plain text when accessed directly.

Installation
------------
Copy `php.php` and the `php` folder to `femto/plugins` then add _PHP_ to the 
list of enabled plugins in `index.php`:

    $config['plugin_enabled'] = 'PHP';

or

    $config['plugin_enabled'] = 'Other_Plugin,PHP';


Usage
-----
Set the `php` flag in the header of the page which contains PHP code.

    /*
    Title: My Page
    Flags: php
    */

    <?php
    // code goes here

The output of your PHP script will be used as content for the page. 
Alternatively, if your script has no output, $page['content'] will be used.

Features and Caveats
--------------------
### no-cache
Content of pages with the `php` flag is never cached, thus setting the 
`no-cache` flag is redundant.

### php-emulate-femto
By default the content of pages with the `php` flag is not processed any
further. If you want to emulate the usual Femto behaviour, then you need to set
the `php-emulate-femto` flag.

This can have a significant impact on performances.

    /*
    Title: My Page
    Flags: php,php-emulate-femto
    */

    A markdown link:
    [<?php echo phpversion(); ?>](http://php.net)

### no-markdown
You can set the `no-markdown` flag in combination with `php-emulate-femto` to
disable markdown parsing and mitigate the performance hit.

Has no effect when Femto emulation isn't active.

### __FILE__ and __DIR__
Since the PHP code is not run directly from the page's file, magic constants
`__FILE__` and `__DIR__` will not work as expected. You can use `$page['file']`
and `$page['directory']['file']` instead.

    // access a file in the same directory as the page
    $file = file_get_contents($page['directory']['file'].'/file.md');

### namespace
PHP pages are run inside the `femto\plugin\php\util` namespace. If you want to
instance a global class you should prefix it with `\`.

    // global class instance
    $object = new \DomDocument();

If you include another file from your page you should add 
`namespace femto\plugin\php\util;` at the top.

### $config
This variable contains the website's configuration as defined in `index.php`.

    // display the website's title
    echo $config['site_title'];

### $page
This variable contains the current page's information as defined in the header.

    // display the page's title
    echo $page['title'];

### Exception
You can throw an `Exception` to trigger an error page.

    // trigger an error
    throw new Exception('The error message');

### Page($url)
Returns the Femto page corresponding to `$url` or `null` if there is none.

    // find a page
    $error_page = page('/404');

### Directory($url)
Returns the Femto directory corresponding to `$url` or an empty directory. Use
the `sort()` method to list the pages inside. Pages returned have no content.

    // list all pages in the content directory
    foreach(directory('/')->sort('alpha', 'asc') as $p) {
        echo $p['title'];
    }

### Redirect($to, $code=303)
Redirects to `$to` with the code `$code`. 

    // redirect to a different femto page
    redirect($other_page['url']);

    // redirect to an arbitrary url with a different code
    redirect('http://php.net', 301);

### Qs($select='*')
Build a query string based on the current one. Optionally select keys to keep or
add.

    // the current query string
    qs();
 
    // selected variable from current query string
    // use * to copy all current variables
    qs('variable_selected');
 
    // new variable
    qs(['new_variable'=>'new_value']);
 
    // combined
    qs(['new_variable'=>'new_value','variable_selected']);

### Escape($string)
Escape a string for use with html. Like in templates, you can also use short
echo tags `<?=` to directly display escaped content and `<?==` to display
unescaped content.

### Form Class
A class to check and persist forms.

Validation is based on the HTML code, for example a field with `type="email"`
will be checked to contain a valid email address. Since modern browsers check 
forms before submission, the class is only intended as a server-side safeguard 
and does not provide the user with explanations as to why a field is invalid.

Persistence is assured upon validation. Submitted data is injected in the form,
ensuring users do not lose the information they entered.

    // create a form object with the form's HTML code
    // code must begin and end with the <form> tag
    $form = new Form('<form>...</form>');

    // check whether the form was submitted and is valid
    // this also trigger persistence if any data was submitted
    if ($form()) {
        // ...
    }

    // display the form
    echo $form;

The constructor accepts an optional `$config` parameter which lets you set the
following options:

* `error_class` - The css class to add to invalid elements.

* `debug` - Whether debug mode is enabled. In debug mode all libxml errors will
be displayed.

The Form class supports multiple forms in the same page, provided they use
different `name` attributes for their respective `submit` elements.

    // create a form object with additional configuration
    $form1 = new Form('<form>...</form>', ['debug'=>True]);

    // create a second form, submit element must use a different name
    $form2 = new Form('<form>...</form>');

    // check whether form1 was submitted and is valid
    if ($form1()) {
        // ...
    }
    // check whether form2 was submitted and is valid
    if ($form2()) {
        // ...
    }

    // display the forms
    echo $form1, $form2;
