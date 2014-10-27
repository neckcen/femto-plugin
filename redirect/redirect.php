<?php

namespace femto\plugin;

/**
 * A Femto plugin. Let you redirect a page to another page or an arbitrary
 * address.
 *
 * @author Sylvain Didelot
 */
class Redirect {
    protected $config;

    /**
     * Instance the plugin with given configuration.
     *
     * @param array $config The website configuration.
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Add redirect headers.
     *
     * @param array $header The list of headers.
     */
    public function page_before_read_header(&$header) {
        $header['redirect'] = null;
        $header['redirect-permanent'] = 'No';
    }

    /**
     * Parse redirect header if present.
     *
     * @param array $page a Femto page.
     */
    public function page_before_parse_content(&$page) {
        if(!empty($page['redirect'])) {
            $url = str_replace('%base_url%', $this->config['base_url'], $page['redirect']);
            $type = strtolower($page['redirect-permanent']) == 'yes' ? 301 : 302;
            $page['redirect'] = array(
                'to' => $url,
                'type' => $type,
            );
        }
    }

    /**
     * Apply redirection.
     *
     * @param array $page a Femto page.
     */
    public function request_complete(&$page) {
        if(!empty($page['redirect'])) {
            header('Location: '.$page['redirect']['to'], true, $page['redirect']['type']);
            exit();
        }
    }
}
