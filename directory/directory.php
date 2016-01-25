<?php

/**
 * A plugin for Femto that let you display a list of pages in a directory.
 *
 * @author Sylvain Didelot
 */
namespace femto\plugin\directory;

/**
 * If requested, list one or more directory.
 *
 * @param Page $page Femto page.
 */
function page_content_after($page) {
    $re = '`(?:<p>)?\{directory:([^,{]*)(?:,([^{,]+))?(?:,([^,{]+))?\}(?:</p>)?`';
    if(!preg_match_all($re, $page['content'], $match, PREG_SET_ORDER)) {
        return;
    }
    foreach($match as $m) {
        $tag = $m[0];
        $sort = isset($m[2]) ? $m[2] : 'alpha';
        $order = isset($m[3]) ? $m[3] : 'asc';

        $dir = \femto\Directory::resolve($m[1])->sort($sort, $order);
        $html = '<ol class="directory">';
        foreach ($dir as $p) {
            $html .= sprintf(
              '<li><a href="%s">%s</a></li>',
              \femto\escape($p['url']), \femto\escape($p['title'])
            );
        }
        $html .= '</ol>';
        $page['content'] = str_replace($tag, $html, $page['content']);
    }
}
