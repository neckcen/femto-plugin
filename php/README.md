PHP plugin for Femto
====================

A plugin to allow PHP code within Femto pages.

Installation
------------
Copy `php.php` to `femto/plugins` then add _PHP_ to the list of enabled plugins
in `index.php`:

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

Notes
-----
If your content folder is visible from the web (which should not be the case), 
then be aware that the PHP code within Femto pages will be displayed as plain 
text when pages files are accessed directly.

PHP code is never cached, thus setting the `no-cache` flag is redundant. For the
same reason, it is recommended to set the `no-markdown` flag when possible to
increase performances.

Your script has access to `$config` which contains the website's configuration
and `$page` which contains the current page's information. If your code sets
`$page['content']` then it will be used and output will be sent directly to the
browser. If `$page['content']` is null then your script's output will be used as
the page's content.

Forms validation and persistence
--------------------------------
This function is available as a separate class: `\femto\plugin\PHP\Form`.

Validation is based on the HTML code, for example a field with `type="email"`
will be checked to contain a valid email address. Since modern browsers check 
forms before submission, the class is only intended as a server-side safeguard 
and does not provide the user with explanations as to why a field is invalid.

Persistence is assured upon validation. Submitted data is injected in the form,
ensuring users do not lose the information they entered.

    // create a form object with the form's HTML code
    // code must begin and end with the <form> tag
    $form = new \femto\plugin\PHP\Form('<form>...</form>');

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
    $form1 = new \femto\plugin\PHP\Form('<form>...</form>', ['debug'=>True]);

    // create a second form, submit element must use a different name
    $form2 = new \femto\plugin\PHP\Form('<form>...</form>');

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
