<?php

namespace femto\plugin;

/**
 * A Femto plugin. To add Date, Excerpt and Author information to pages.
 *
 * @author Sylvain Didelot
 */
class Page_Extra {
    /**
     * Default configuration.
     *
     * @var array
     */
    public static $default = [
        'page_extra_date_format' => 'jS M Y',
        'page_extra_excerpt_length' => 50,
    ];

    /**
     * This instance's configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Instance the plugin with given config.
     * Possible config keys:
     *  page_extra_date_format - php date's format to use
     *  page_extra_excerpt_length - length (in words) of the exerpt
     *
     * @see http://php.net/manual/en/function.date.php
     *
     * @param array $config The website configuration.
     */
    public function __construct($config) {
        $this->config = $config + self::$default;
    }

    /**
     * Add date and author headers.
     *
     * @param array $header The list of headers.
     */
    public function page_parse_header_before(&$header) {
        $header['date'] = null;
        $header['author'] = null;
        $header['order'] = null;
    }

    /**
     * Parse date and add excerpt to page info.
     *
     * @param array $page a Femto page.
     */
    public function page_parse_content_after(&$page) {
        if(!empty($page['date'])) {
            $page['timestamp'] = strtotime($page['date']);
            $format = $this->config['page_extra_date_format'];
            $page['date_formatted'] = date($format, $page['timestamp']);
        } else {
            $page['timestamp'] = null;
            $page['date_formatted'] = null;
        }

        $max = $this->config['page_extra_excerpt_length'];
        $page['excerpt'] = explode(' ', strip_tags($page['content']), $max+1);
        if(count($page['excerpt']) > $max) {
            $page['excerpt'][$max+1] = '';
            $page['excerpt'] = trim(implode(' ', $page['excerpt'])).'…';
        } else {
            $page['excerpt'] = $page['content'];
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
    public function directory_sort(&$dir, &$sort) {
        if($sort == 'date') {
            usort($dir, [$this, 'directory_sort_date']);
        } else if($sort == 'order') {
            usort($dir, [$this, 'directory_sort_order']);
        }
    }

    /**
     * Used to sort directory by date.
     *
     * @see usort()
     *
     * @param array $a Page a.
     * @param array $b Page b.
     * @return int 0 if equal, 1 if a > b, -1 if b > a.
     */
    public function directory_sort_date($a, $b) {
        return $a['timestamp'] == $b['timestamp'] ? 0 :
            ($a['timestamp'] < $b['timestamp'] ? -1 : 1);
    }

    /**
     * Used to sort directory by order.
     *
     * @see usort()
     *
     * @param array $a Page a.
     * @param array $b Page b.
     * @return int 0 if equal, 1 if a > b, -1 if b > a.
     */
    public function directory_sort_order($a, $b) {
        return $a['order'] == $b['order'] ? 0 :
            ($a['order'] < $b['order'] ? -1 : 1);
    }
}
