<?php

namespace femto\plugin\php;

/**
 * A plugin for Femto that allows PHP in pages.
 *
 * A note on security: If your content folder is visible from the web (which
 * should not be the case), then be aware that the php within femto pages will
 * be displayed in plain text as the ".md" extension does not trigger php
 * execution.
 *
 * @author Sylvain Didelot
 */


/**
 * If the php flag is set, store the page's content as a script in the cache
 * directory.
 *
 * @param array $page Femto page.
 */
function page_content_before($page) {
    // only act for php pages and avoid loop when php-emulate-femto is on
    if(!in_array('php', $page['flags']) || isset($page['php_file'])) return;

    // copy the content to a separate file
    $cache = new \femto\FileCache($page['file'], 'plugin_php', ['raw'=>true]);
    if(!$cache->valid()) {
        // insert namespace and calculate how much space the header takes which
        // allows for accurate line numbers in case of errors
        $content = file_get_contents($page['file']);
        $start = strpos($content, $page['content']);
        $lines = substr_count($content, "\n", 0, $start);
        $content = '<?php namespace femto\plugin\php\util;';
        for ($i=0; $i < $lines; $i++) {
            $content .= "\n";
        }
        $content .= '?>'.$page['content'];
        $content = str_replace('<?==', '<?php echo ', $content);
        /* this ugly thing matches echo tags without tripping on things like
           <?=$x['?>']?> or <?=(true)?'a':b'?>
        */
        $content = preg_replace(
            '`<\?='.
            '((?:[^;\?"\']|\?[^>]|"(?:[^"]|\\\\")*"|\'(?:[^\']|\\\\\')*\')+)'.
            '((?:;[^;\?"\']|\?[^>]|"(?:[^"]|\\\\")*"|\'(?:[^\']|\\\\\')*\')*)'.
            '\?>`', '<?php echo escape($1); $2 ?>', $content);
        $content = str_replace('?><?php', '', $content);
        $cache->store($content);
    }

    // ensure cache is activated as script's output isn't cached any way.
    $nocache = array_search('no-cache', $page['flags']);
    if($nocache !== false) {
        unset($page['flags'][$nocache]);
    }
    // blank content
    $page['content'] = '';
    $page['php_file'] = (string) $cache;
}

/**
 * If the php flag is set, include the previously stored script.
 *
 * @param array $page Femto page.
 */
function request_complete($page) {
    // only act for php pages
    if(!in_array('php', $page['flags'])) return;

    // make goodies available
    require __DIR__.'/php/util.php';
    // include the script created earlier
    $config = \femto\Femto::$config;
    ob_start();
    $return = include $page['php_file'];
    $content = ob_get_clean();

    // if return isn't null or true assume an error
    if($return != null && $return != True) {
        header('Internal Server Error', true, 500);
        if(is_array($return)) {
            $page['title'] = isset($return[0]) ? $return[0] : 'Error 500';
            $page['content'] = isset($return[1]) ? $return[1] : 'Error';
        } else {
            $page['title'] = 'Error 500';
            $page['content'] = $return;
        }
        return;
    }
    // if $content isn't empty then use it as page's content
    if(!empty($content)) {
        $page['content'] = $content;
    }
    // Treat $page['content'] like femto would if php-emulate-femto is set.
    // As php scripts are not cached, using markdown is not recommended
    // (set the no-markdown flag to disable markdown parsing).
    if(in_array('php-emulate-femto', $page['flags'])) {
        $page->content();
    }
}
