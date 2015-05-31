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
    public function page_parse_header_before(&$header) {
        $header['redirect'] = null;
    }

    /**
     * Parse redirect header if present.
     *
     * @param array $page a Femto page.
     */
    public function page_parse_header_after(&$page) {
        if(!empty($page['redirect'])) {
            $url = str_replace('%base_url%', $this->config['base_url'], $page['redirect']);
            $type = in_array('redirect-permanent', $page['flags']) ? 301 : 302;
            $page['redirect'] = [
                'to' => $url,
                'type' => $type,
            ];
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
