<?php

namespace femto\plugin;

/**
 * A plugin for Femto to render pages as PDF.
 *
 * @author Sylvain Didelot
 */
class PDF {
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
        // get the page from femto
        $page = \femto\page('/'.$url);
        if($page == null) {
            return;
        }

        // last modified header (so browsers can cache the PDF)
        $time = @filemtime($page['file']);
        if($time == false) {
            return;
        }
        $header = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ?
          strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : 0;
        if($header >= $time) {
            header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
            exit();
        }
        header('Last-Modified: '.date(DATE_RFC1123, $time));

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
            // add domain and protocol if needed
            if(substr($page['url'], 0, 1) == '/') {
                $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
                $page['url'] = sprintf('%s://%s:%d/%s', $protocol,
                  $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $page['url']);
            }

            // attempt to create pdf
            require __DIR__.'/pdf/dompdf_config.inc.php';
            $dompdf = new \DOMPDF();
            $dompdf->load_html_file($page['url']);
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
