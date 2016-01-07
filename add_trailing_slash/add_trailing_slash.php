<?php

namespace femto\plugin;

/**
 * A Femto plugin. When an user access an url without a trailing slash which
 * doesn't exists but a subfolder with this name exists, redirect them to it.
 *
 * @author Sylvain Didelot
 */
class Add_Trailing_Slash {
    /**
     * Configuration.
     *
     * @var array
     */
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
     * Redirect to the subfolder if it exists.
     *
     * @param string $url The url which didn't match any page
     * @param array $page The current page
     */
    public function request_not_found(&$url, &$page) {
        if(substr($url, -1) != '/') {
            $page = \femto\page($url.'/');
            if($page) {
                header('Location: '.$this->config['base_url'].$page['url'], true, 301);
                exit();
            }
        }
    }
}
