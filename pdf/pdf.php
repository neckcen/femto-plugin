<?php

namespace femto\plugin;

/**
 * A plugin for Femto to render pages as PDF.
 *
 * @see https://github.com/dompdf/dompdf
 *
 * @author Sylvain Didelot
 */
class PDF {
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
     * Render a page as PDF.
     *
     * @param string $url The url of the page (relative to content dir).
     */
    public function url($url) {
        // make sure the page exists
        $page = \femto\page('/'.$url);
        if($page == null) {
            return;
        }

        // check for cache
        $cache = new \femto\FileCache($page['file'], 'pdf', ['raw'=>True]);
        if(($data = $cache->retrieve()) === null) {
            if(in_array('no-theme', $page['flags'])) {
                $data = $page['content'];
            } else {
                $template = new \femto\Template($page['template'].'.html.php');
                $template['page'] = $page;
                $data = (string) $template;
            }

            // build fully qualified base url
            $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
            $base_url = $this->config['base_url'];
            if(substr($base_url, 0, 2) == '//') {
                $base_url = $protocol.':'.$base_url;
            } else if(substr($base_url, 0, 1) == '/' || $base_url == '') {
                $http_auth = isset($_SERVER['PHP_AUTH_USER']) ?
                  $_SERVER['PHP_AUTH_USER'] : '';
                if(isset($_SERVER['PHP_AUTH_PW'])) {
                  $http_auth .= ':'.$_SERVER['PHP_AUTH_PW'];
                }
                if($http_auth != '') {
                    $http_auth .= '@';
                }
                $port = ':'.$_SERVER['SERVER_PORT'];
                if(($protocol == 'http' && $port == ':80') ||
                  ($protocol == 'https' && $port == ':443')) {
                    $port = '';
                }
                $base_url = sprintf('%s://%s%s%s%s', $protocol, $http_auth,
                  $_SERVER['SERVER_NAME'], $port, $base_url);
            }

            // create pdf
            require __DIR__.'/pdf/dompdf_config.inc.php';
            $dompdf = new \DOMPDF();
            $dompdf->load_html_file($base_url.$page['url']);
            $dom = $dompdf->get_dom();
            foreach($dom->getElementsByTagName('a') as $link) {
                $href = $link->getAttribute('href');
                if(substr($href, 0, 2) == '//') {
                    $link->setAttribute('href', $protocol.':'.$href);
                } else if (substr($href, 0, 1) == '/') {
                    $link->setAttribute('href', $base_url.$href);
                }
            }
            $dompdf->render();
            $data = $dompdf->output();
            $cache->store($data);
        }

        // display PDF
        header('Content-type: application/pdf');
        echo $data;
        exit();
    }
}
