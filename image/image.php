<?php

/**
 * A plugin for Femto to make working with images easier.
 *
 * @author Sylvain Didelot
 */

namespace femto\plugin\image;

/**
 * Display an image or thumbnail.
 *
 * @param string $url The plugin-specific url.
 */
function url($url) {
    $file = \femto\url_to_file('/'.$url);
    if(!$file) return;
    // last modified header (so browsers can cache images)
    $time = filemtime($file);
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
        return;
    }
    //get info for thumbnail
    $width = (int) $_GET['w'];
    $height = isset($_GET['h']) ? (int) $_GET['h'] : null;

    // create thumbnail
    $cache = new \femto\Cache('plugin_image'.$file.$width.$height, ['raw'=>true]);
    if(!$cache->valid($time)) {
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
        $cache->store(null); // make sure file exists
        imagejpeg($thumb, (string) $cache, 65);
        imagedestroy($img);
        imagedestroy($thumb);
    }

    // display image
    header('Content-type: image/jpg');
    readfile($cache);
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
 * @param Page $page Femto page.
 */
function page_content_after($page) {
    $re = '`(<p>)?<img src="([^"]+)" alt="([^"]*)" '.
      '(?:title="([^"]+)" )?/>'.
      '(?:\[( ?)([0-9]+)(?:x([0-9]+))?( ?)\])?(</p>)?`';
    if(!preg_match_all($re, $page['content'], $match, PREG_SET_ORDER)) {
        return;
    }
    $url = \femto\escape(\femto\Femto::$config['base_url'].'/plugin/image');
    foreach ($match as $m) {
        list($tag, $p1, $src, $alt) = $m;
        // ignore non-local url
        if(preg_match('`^(?:[a-z][a-z0-9+-.]*:)?//`i', $src)) continue;
        $src = \femto\real_url($src, $page['directory']['url'], false);
        if($src === null) continue;
        $src = $url.\femto\escape($src);

        $title = isset($m[4]) ? $m[4] : '';
        $align1 = isset($m[5]) ? $m[5] : '';
        $width = isset($m[6]) ? $m[6] : 0;
        $height = isset($m[7]) ? $m[7] : 0;
        $align2 = isset($m[8]) ? $m[8] : '';
        $p2 = isset($m[9]) ? $m[9] : '';

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
              '<figure class="%s" style="width:%dpx;"><a href="%s">'.
              '<img src="%s?w=%d&amp;h=%d" alt="%s"/></a>%s</figure>',
              $align, $width, $src, $src, $width, $height, $alt, $title
            );
            // figure can't go inside <p>
            if($p1 == '' && $p2 == '') {
                $parsed = '</p>'.$parsed.'<p>';
            } else if ($p1 != '' && $p2 == '') {
                $parsed = $parsed.'<p>';
            } else if ($p1 == '' && $p2 != '') {
                $parsed = '</p>'.$parsed;
            }
        } else {
            $parsed = sprintf(
              '<img src="%s" alt="%s" title="%s"/>',
              $src, $alt, $title
            );
            $parsed = $p1.$parsed.$p2;
        }
        $page['content'] = str_replace($tag, $parsed, $page['content']);
    }
}
