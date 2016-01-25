<?php

/**
 * A plugin for Femto to render pages as PDF.
 *
 * @see https://github.com/dompdf/dompdf
 *
 * @author Sylvain Didelot
 */
namespace femto\plugin\pdf;



/**
 * Render a page as PDF.
 *
 * @param string $url The url of the page (relative to content dir).
 */
function url($url) {
    // make sure the page exists
    $page = \femto\Page::resolve('/'.$url);
    if($page == null) {
        return;
    }

    // check for cache
    $cache = new \femto\FileCache($page['file'], 'plugin_pdf', ['raw'=>True]);
    if(($data = $cache->retrieve()) === null) {

        // build fully qualified url
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
        $url = sprintf('%s://%s%s%s%s', $protocol, $http_auth,
          $_SERVER['SERVER_NAME'], $port, $page['url']);

        // create pdf
        require __DIR__.'/pdf/dompdf_config.inc.php';
        $dompdf = new \DOMPDF();
        $dompdf->load_html_file($url);
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

function page_content_after($page) {
    $url = \femto\Femto::$config['base_url'].'/plugin/pdf';
    $page['content'] = str_replace('pdf://self', $url.$page['url'], $page['content']);
    $page['content'] = str_replace('pdf://', $url.'/', $page['content']);
}
