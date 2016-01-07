Add Trailing Slash plugin for Femto
===================================

A plugin to redirect to a subfolder when people ommit the trailing slash.

Installation
------------
Copy `add_trailing_slash.php` to `femto/plugins` then add 
<em>Add_Trailing_Slash</em> to the list of enabled plugins in `index.php`:

    $config['plugin_enabled'] = 'Add_Trailing_Slash';

or

    $config['plugin_enabled'] = 'Other_Plugin,Add_Trailing_Slash';

Usage
-----
When users access a page which doesn't exists, but a subfolder with the same
name exists, they will be redirected.

Consider the following content folder:

Physical Location           | URL
--------------------------- | --------------------------------
content/index.md            | /
content/sub.md              | /sub
content/sub/index.md        | /sub/
content/sub2/index.md       | /sub2/

When users access `/sub` they see the content of `content/sub.md`. When they
access `/sub/` they see `content/sub/index.md`. The plugin has no effect there.

However when they access `/sub2`, they would usually get an error saying the
page does not exist. If the plugin is active they will be redirected to `/sub2/`
instead.
