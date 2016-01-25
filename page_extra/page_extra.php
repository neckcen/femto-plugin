<?php

/**
 * A Femto plugin. To add Date, Author information and css class to pages.
 *
 * @author Sylvain Didelot
 */

namespace femto\plugin\page_extra;


function config(&$config) {
    $default = [
        'page_extra_date_format' => 'jS M Y',
    ];
    $config = $config + $default;

    \femto\Page::$header = \femto\Page::$header + [
        'date' => null,
        'author' => null,
        'order' => null,
    ];
}

/**
 * Parse date and add excerpt to page info.
 *
 * @param array $page a Femto page.
 */
function page_header($page) {
    if(!empty($page['date'])) {
        $page['timestamp'] = strtotime($page['date']);
        $format = \femto\Femto::$config['page_extra_date_format'];
        $page['date_formatted'] = date($format, $page['timestamp']);
    } else {
        $page['timestamp'] = null;
        $page['date_formatted'] = null;
    }

    $class = strtolower(str_replace(' ', '_', $page['title']));
    $class = preg_replace('`[^-_a-z0-9]`', '', $class);
    $page['class'] = 'page_'.$class;
}

/**
 * Add date/order sorting to directory sort.
 *
 * @param array $dir The directory listing to sort.
 * @param string $sort Sorting criteria.
 */
function directory_sort(&$dir, &$sort) {
    if($sort == 'date') {
        usort($dir, function($a, $b) {
            return $a['timestamp'] == $b['timestamp'] ? 0 :
              ($a['timestamp'] < $b['timestamp'] ? -1 : 1);
        });
    } else if($sort == 'order') {
        usort($dir, function($a, $b){
            return $a['order'] == $b['order'] ? 0 :
              ($a['order'] < $b['order'] ? -1 : 1);
        });
    }
}
