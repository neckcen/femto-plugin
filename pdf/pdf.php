<?php

namespace femto\plugin;

/**
 * A plugin for Femto to render pages as PDF.
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
        $data = null;
        if($this->config['cache_enabled']) {
            $hash = md5($page['file']);
            $cache = sprintf(
              '%s/pdf/%s/%s/%s.pdf',
              $this->config['cache_dir'],
              substr($hash, 0,2), substr($hash, 2,2), $hash
            );
            if(@filemtime($cache) > $time) {
                $data = file_get_contents($cache);
            }
        }
        if($data == null) {
            // build fully qualified base url
            $base_url = $this->config['base_url'];
            if(substr($base_url, 0, 2) == '//') {
                $base_url = isset($_SERVER['HTTPS']) ?
                  'https:'.$base_url : 'http:'.$base_url;
            } else if(substr($base_url, 0, 1) == '/' || $base_url == '') {
                $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
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

            // attempt to create pdf
            require __DIR__.'/pdf/dompdf_config.inc.php';
            $dompdf = new \DOMPDF();
            $dompdf->load_html_file($base_url.$page['url']);
            // fix links
            $dom = $dompdf->get_dom();
            foreach($dom->getElementsByTagName('a') as $link) {
                $href = $link->getAttribute('href');
                if(substr($href, 0, 2) == '//') {
                    $href = $dompdf->get_protocol().substr($href, 2);
                    $link->setAttribute('href', $href);
                } else if (substr($href, 0, 1) == '/') {
                    $link->setAttribute('href', $base_url.$href);
                }
            }
            $dompdf->render();
            $data = $dompdf->output();
            if($this->config['cache_enabled']) {
                @mkdir(dirname($cache), 0777, true);
                file_put_contents($cache, $data);
            }
        }

        // display image
        header('Content-type: application/pdf');
        echo $data;
        exit();
    }
}
