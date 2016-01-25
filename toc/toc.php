<?php

namespace femto\plugin\toc;

/**
 * A plugin for Femto that let you display a table of content.
 *
 * @author Sylvain Didelot
 */
/**
 * If requested, find all titles in the page and produce a table of content.
 *
 * @param array $page Femto page.
 */
function page_content_after($page) {
    $re = '`(?:<p>)?\{TOC(?::([0-9])(?:,([0-9]))?)?\}(?:</p>)?`';
    if(!preg_match($re, $page['content'], $match)) {
        return;
    }
    $tag = $match[0];
    $min = isset($match[1]) ? min(max($match[1], 1), 6) : 1;
    $max = isset($match[2]) ? min(max($match[2], 1), 6) : 6;
    if($min > $max) {
        $max = $min;
    }

    $match = [];
    $re = sprintf('`<h([%d-%d])>([^<]*)</h[%d-%d]>`', $min, $max, $min, $max);
    preg_match_all($re, $page['content'], $match, PREG_SET_ORDER);
    $current_level = $min;
    $toc = '<nav class="toc"><ol>';
    foreach ($match as $m) {
        list($htag, $level, $title) = $m;
        $id = preg_replace('`[^-_ 0-9a-zA-Z]`', '', $title);
        $id = str_replace(' ', '_', trim($id));
        $tag_id = sprintf(
          '<h%d id="%s">%s</h%d>',
          $level, $id, $title, $level
        );
        $page['content'] = str_replace($htag, $tag_id, $page['content']);
        while($level > $current_level) {
            $toc .= '<li><ol>';
            $current_level++;
        }
        while($level < $current_level) {
            $toc .= '</ol></li>';
            $current_level--;
        }
        $toc .= sprintf(
          '<li><a href="#%s">%s</a></li>',
          $id, $title
        );
    }
    while($current_level > $min) {
        $toc .= '</ol></li>';
        $current_level--;
    }
    $toc .= '</ol></nav>';
    $page['content'] = str_replace($tag, $toc, $page['content']);
}
