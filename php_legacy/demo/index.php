<?php

// Page information is available in $page for example $page['content'].
// $vars's values will be substitued in the page's content automatically.
$vars['version'] = PHP_VERSION;

// check if a form is valid
if(!empty($forms['testform'])) {
    // form is valid, do something
}
// you can also use numeric index if the form doesn't have any ID
if(!empty($forms[0])) {
    // form is valid, do something
}
