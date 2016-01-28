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
        for ($i=0; $i <= $lines; $i++) {
            $content .= "\n";
        }
        $content .= '?>'.$page['content'];
        $token = token_get_all($content);
        $content = '';
        $escaping = false;
        foreach($token as $i => $t) {
            if(is_array($t)) {
                if($t[0] === T_OPEN_TAG_WITH_ECHO) {
                    // don't reopen if last tag was closing
                    if(isset($token[$i-1][0]) && $token[$i-1][0] === T_CLOSE_TAG) {
                        $content .= 'echo ';
                    } else {
                        $content .= $t[1];
                    }
                    if(!isset($token[$i+1]) || $token[$i+1] !== '=') {
                        $content .= 'escape(';
                        $escaping = true;
                    }

                } else if ($t[0] === T_OPEN_TAG) {
                    // don't reopen if last tag was closing
                    if(!isset($token[$i-1][0]) || $token[$i-1][0] !== T_CLOSE_TAG) {
                        $content .= $t[1];
                    }

                } else if ($t[0] === T_CLOSE_TAG) {
                    // end escaping
                    if($escaping) {
                        $content .= ')';
                        $escaping = false;
                    }
                    // don't close if next tag is an opening
                    if(!isset($token[$i+1][0])
                      || ($token[$i+1][0] !== T_OPEN_TAG && $token[$i+1][0] !== T_OPEN_TAG_WITH_ECHO)) {
                        $content .= $t[1];
                    } else {
                        // ensure instructions are properly closed with semi colon
                        if(isset($token[$i-1][0]) && $token[$i-1][0] === T_WHITESPACE) {
                            $last = isset($token[$i-2]) ? $token[$i-2] : false;
                        } else {
                            $last = isset($token[$i-1]) ? $token[$i-1] : false;
                        }
                        if($last && !in_array($last, [';', ':', '}'])) {
                            $content .= ';';
                        }
                    }
                } else {
                    $content .= $t[1];
                }
            } else {
                if($t === '=' && isset($token[$i-1][0]) && $token[$i-1][0] === T_OPEN_TAG_WITH_ECHO) {
                    continue;
                }
                if($t === ';' && $escaping) {
                    $content .= ')';
                    $escaping = false;
                }
                $content .= $t;
            }
        }
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
    try {
        $config = \femto\Femto::$config;
        ob_start();
        include $page['php_file'];
        $content = ob_get_clean();
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
    } catch (util\Exception $e) {
        header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
        $page['title'] = 'Error 500';
        $page['content'] = $e->getMessage().'<pre>'.$e->getTraceAsString().'</pre>';
    }
}
