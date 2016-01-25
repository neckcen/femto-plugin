<?php
/**
 * A Femto plugin. When an user access an url without a trailing slash which
 * doesn't exists but a subfolder with this name exists, redirect them to it.
 *
 * @author Sylvain Didelot
 */

namespace femto\plugin\add_trailing_slash;

/**
 * Redirect to the subfolder if it exists.
 *
 * @param string $url The url which didn't match any page
 * @param Page $page The current page
 */
function request_not_found(&$url, $page) {
    if(substr($url, -1) != '/') {
        $page = \femto\Page::resolve($url.'/');
        if($page) {
            header('Location: '.$page['url'], true, 301);
            exit();
        }
    }
}
