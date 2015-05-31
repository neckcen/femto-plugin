<?php

namespace femto\plugin;

/**
 * A plugin for Femto to make working with images easier.
 *
 * @author Sylvain Didelot
 */
class Image {
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
     * Display an image or thumbnail.
     *
     * @param string $url The plugin-specific url.
     */
    public function url($url) {
        $file = $this->config['content_dir'].$url;
        // last modified header (so browsers can cache images)
        $time = @filemtime($file);
        if($time == false) {
            return null;
        }
        $header = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ?
          strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : 0;
        if($header >= $time) {
            header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
            exit();
        }
        header('Last-Modified: '.date(DATE_RFC1123, $time));

        // width not set, display the full image
        if(empty($_GET['w'])) {
            $info = @getimagesize($file);
            if($info) {
                header('Content-type: '.$info['mime']);
                readfile($file);
                exit();
            }
        }
        //get info from GET
        $width = (int) $_GET['w'];
        $height = isset($_GET['h']) ? (int) $_GET['h'] : null;

        // create thumbnail
        $cache = \femto\Cache::file($file, $width.$height);
        if(($data = $cache->retrieve()) == null) {
            // check target exists and is an image
            $type = @exif_imagetype($file);
            if($type === false) {
                return null;
            }

            if($type == IMAGETYPE_JPEG) {
                $img = imagecreatefromjpeg($file);
            } else if ($type == IMAGETYPE_PNG) {
                $img = imagecreatefrompng($file);
            } else if ($type == IMAGETYPE_GIF) {
                $img = imagecreatefromgif($file);
            } else {
                return null;
            }
            if($img == false) {
                return null;
            }

            // create thumbnail
            if(!$height) {
                $height = round($width/(imagesx($img)/imagesy($img)));
            }
            $thumb = imagecreatetruecolor($width,$height);
            imagecopyresampled(
                $thumb, $img,
                0, 0, 0, 0,
                $width, $height, imagesx($img), imagesy($img)
            );

            // save it and destroy resources
            ob_start();
            imagejpeg($thumb, null, 65);
            $data = base64_encode(ob_get_clean());
            $cache->store($data);
            imagedestroy($img);
            imagedestroy($thumb);
        }

        // display image
        header('Content-type: image/jpg');
        echo base64_decode($data);
        exit();
    }

    /**
     * Search for images and process them.
     * File names starting with "http(s)://", "ftp://", "//" or "/" are ignored.
     * File names starting with "content://" will be treated as relative to
     * content_dir. Other names are looked up in the current page's directory.
     *
     * Examples:
     * ![alt text](http://example.tld/file.jpg) - unchanged
     * ![alt text](/file.jpg) - unchanged
     * ![alt text](content://file.jpg) - matched to content_dir/file.jpg
     * ![alt text](file.jpg) - matched to current_page_dir/file.jpg
     *
     * Additionally, a dimension can be put after the file name to create a
     * thumbnail. Only works with local files.
     *
     * Examples:
     * ![alt text](http://example.tld/file.jpg)[300x200] - ignored
     * ![alt text](file.jpg)[300] - 300 width thumbnail, calculate height
     * ![alt text](file.jpg)[300x200] - 300x200 thumbnail, ratio ignored
     * ![alt text](file.jpg "caption text")[300] thumbnail with caption
     *
     * You can also specify the alignement by putting a space left or right.
     *
     * Examples:
     * ![alt text](file.jpg)[ 300] - Aligned right.
     * ![alt text](file.jpg)[300 ] - Aligned left.
     *
     * @param array $page Femto page.
     */
    public function page_parse_content_after(&$page) {
        $match = [];
        $re = '`(<p>)?<img src="([^"]+)" alt="([^"]*)" '.
          '(?:title="([^"]+)" )?/>'.
          '(?:\[( ?)([0-9]+)(?:x([0-9]+))?( ?)\])?(</p>)?`';
        if(preg_match_all($re, $page['content'], $match, PREG_SET_ORDER)) {
            $url = $this->config['base_url'].'/plugin/image';
            foreach ($match as $m) {
                list($tag, $p1, $src, $alt) = $m;
                if(preg_match('`^(https?:/|ftp:/|/)?/`', $src)) {
                    continue;
                }
                $title = isset($m[4]) ? $m[4] : '';
                $align1 = isset($m[5]) ? $m[5] : '';
                $width = isset($m[6]) ? $m[6] : 0;
                $height = isset($m[7]) ? $m[7] : 0;
                $align2 = isset($m[8]) ? $m[8] : '';
                $p2 = isset($m[9]) ? $m[9] : '';
                if(substr($src, 0, 10) == 'content://') {
                    $src = substr($src, 9);
                } else {
                    $src = dirname($page['file']).'/'.$src;
                    $src = substr($src, strlen($this->config['content_dir']));
                }
                if($width) {
                    $align = 'center';
                    if($align1 == ' ' && $align2 == '') {
                        $align = 'right';
                    } else if($align1 == '' && $align2 == ' ') {
                        $align = 'left';
                    }
                    if($title) {
                        $title = '<figcaption>'.$title.'</figcaption>';
                    }
                    $parsed = sprintf(
                      '<figure class="%s" style="width:%dpx;"><a href="%s/%s">'.
                      '<img src="%s/%s?w=%d&amp;h=%d" alt="%s"/></a>%s</figure>',
                      $align, $width, $url, $src,
                      $url, $src, $width, $height, $alt, $title
                    );
                } else {
                    $parsed = sprintf(
                      '<img src="%s/%s" alt="%s" title="%s"/>',
                      $url, $src, $alt, $title
                    );
                }
                if($p1 != '' && $p2 == '') {
                    $tag = substr($tag, 3);
                }
                $page['content'] = str_replace($tag, $parsed, $page['content']);
            }
        }

    }
}
