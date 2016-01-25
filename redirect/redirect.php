<?php

namespace femto\plugin\redirect;

/**
 * A Femto plugin. Let you redirect a page to another page or an arbitrary
 * address.
 *
 * @author Sylvain Didelot
 */


/**
 * Add redirect headers.
 *
 * @param array $header The list of headers.
 */
function config(&$config) {
    \femto\Page::$header['redirect'] = null;
}

/**
 * Apply redirection.
 *
 * @param array $page a Femto page.
 */
function request_complete($page) {
    if(!empty($page['redirect'])) {
        $page['redirect'] = str_replace('femto://self', $page['url'], $page['redirect']);
        $url = isset($page['directory']) ? $page['directory']['url'] : '';
        $page['redirect'] = str_replace('femto://directory', $url, $page['redirect']);
        $page['redirect'] = str_replace('femto://', \femto\Femto::$config['base_url'].'/', $page['redirect']);
        if(!preg_match('`^(?:[a-z][a-z0-9+-.]*:)?//`i', $page['redirect'])) {
            $page['redirect'] = \femto\real_url($page['redirect'], $page['directory']['url']);
        }
        $type = in_array('redirect-permanent', $page['flags']) ? 301 : 302;
        header('Location: '.$page['redirect'], true, $type);
        exit();
    }
}
