<?php

namespace femto\plugin;

/**
 * A plugin for Femto that let you display a list of pages in a directory.
 *
 * @author Sylvain Didelot
 */
class Directory {
    /**
     * If requested, list one or more directory.
     *
     * @param array $page Femto page.
     */
    public function page_parse_content_after(&$page) {
        $match = [];
        $re = '`(?:<p>)?%directory:([^,%]+)(?:,([^%,]+))?(?:,([^,%]+))?%(?:</p>)?`';
        if(!preg_match_all($re, $page['content'], $match, PREG_SET_ORDER)) {
            return;
        }
        foreach($match as $m) {
            $tag = $m[0];
            $sort = isset($m[2]) ? $m[2] : 'alpha';
            $order = isset($m[3]) ? $m[3] : 'asc';

            $directory = \femto\directory($m[1], $sort, $order);

            $html = '<ol class="directory">';
            foreach ($directory as $d) {
                $html .= sprintf(
                  '<li><a href="%s">%s</a></li>',
                  $d['url'], $d['title']
                );
            }
            $html .= '</ol>';
            $page['content'] = str_replace($tag, $html, $page['content']);
        }
    }
}
