<?php

namespace femto\plugin {

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
class PHP {
    /**
     * Configuration.
     *
     * @var array
     */
    public $config;

    /**
     * Instance the plugin with given configuration.
     *
     * @param array $config The website configuration.
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * If the php flag is set, store the page's content as a script in the cache
     * directory.
     *
     * @param array $page Femto page.
     */
    public function page_parse_content_before(&$page) {
        // only act for php pages and avoid loop when php-emulate-femto is on
        if(!in_array('php', $page['flags']) || isset($page['php_emulate'])) {
            return;
        }

        // copy the content to a separate file
        $hash = md5($page['file']);
        $file = sprintf('%s/php/%s/%s/%s.php',
          $this->config['cache_dir'],
          substr($hash, 0, 2),
          substr($hash, 2, 2),
          $hash
        );
        @mkdir(dirname($file), 0777, true);
        file_put_contents($file, $page['content']);

        // ensure cache is activated as script's output isn't cached any way.
        $nocache = array_search('no-cache', $page['flags']);
        if($nocache !== false) {
            unset($page['flags'][$nocache]);
        }
        // ensure no-markdown is set but keep previous set state in
        // 'php-no-markdown'
        $page['flags'][] = in_array('no-markdown', $page['flags']) ?
          'php-no-markdown' : 'no-markdown';
        // blank content
        $page['content'] = '';
        $page['php_file'] = $file;
    }

    /**
     * If the php flag is set, include the previously stored script.
     *
     * @param array $page Femto page.
     */
    public function request_complete(&$page) {
        // only act for php pages
        if(!in_array('php', $page['flags'])) {
            return;
        }
        // include the script created earlier
        $config = $this->config;
        ob_start();
        include $page['php_file'];
        // if the script does not set $page['content'] then assume the output is
        // the content
        if(empty($page['content'])) {
            $page['content'] = ob_get_clean();
        } else {
            ob_end_flush();
        }
        // Treat $page['content'] like femto would if php-emulate-femto is set.
        // As php scripts are not cached, using markdown is not recommended
        // (set the no-markdown flag to disable markdown parsing).
        if(in_array('php-emulate-femto', $page['flags'])) {
            $page['php_emulate'] = True;
            \femto\hook('page_parse_content_before', [&$page]);
            $page['content'] = str_replace('%base_url%', $this->config['base_url'], $page['content']);
            $page['content'] = str_replace('%dir_url%', $page['dir_url'], $page['content']);
            $page['content'] = str_replace('%self_url%', $page['url'], $page['content']);
            if(!in_array('php-no-markdown', $page['flags'])) {
                $page['content'] = \Michelf\MarkdownExtra::defaultTransform($page['content']);
            }
            \femto\hook('page_parse_content_after', [&$page]);
        }
    }
}

}
namespace femto\plugin\PHP {

/**
 * Class to make forms easier.
 *
 * Usage:
 *
 * // Create a form object. Code must begin and end with the <form> tag.
 * $form = new Form('<form>...</form>');
 *
 * // validate the form
 * // validating automatically persists data submited
 * if($form()) {
 *     // form was submited and is valid, process it
 * }
 *
 * // display the form
 * echo $form;
 *
 */
class Form {
    /**
     * Default configuration.
     * - error_class: the css class added to invalid elements
     * - debug: whether debug is enabled (displays libxml errors)
     *
     * @var array
     */
    public static $default = [
        'error_class' => 'input-error',
        'debug' => False,
    ];

    /**
     * This instance's configuration.
     *
     * @var array
     */
    public $config;

    /**
     * Invalid elements.
     *
     * @var array
     */
    protected $invalid;

    /**
     * Whether this was the form submited.
     *
     * @var bool
     */
    protected $active;

    /**
     * The form's html code.
     *
     * @var string
     */
    protected $html;

    /**
     * Instance the plugin with given configuration.
     *
     * @param string $html The form's html code
     * @param array $config Configuration
     */
    public function __construct($html, $config=[]) {
        $this->config = $config + self::$default;
        $this->html = $html;
    }

    /**
     * Return the form's html code.
     *
     * @return string
     */
    public function __toString() {
        return $this->html;
    }

    /**
     * Validate the form.
     *
     * @return bool True if the form has been submited and is valid, false otherwise
     */
    public function __invoke() {
        if($_SERVER['REQUEST_METHOD'] != 'POST') {
            return false;
        }

        $this->invalid = [];
        $this->active = null;

        $document = new \DOMDocument();
        $document->encoding = 'UTF-8';
        if(!$this->config['debug']) {
            $xmlerror = libxml_use_internal_errors(true);
        }
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$this->html);
        if(!$this->config['debug']) {
            libxml_use_internal_errors($xmlerror);
        }
        $form = $document->getElementsByTagName('form')->item(0);
        $this->validate($form);
        if($this->active === null || $this->active === True) {
            foreach($this->invalid as $node) {
                $css = explode(' ', $node->getAttribute('class'));
                if(!in_array($this->config['error_class'], $css)) {
                    $css[] = $this->config['error_class'];
                }
                $node->setAttribute('class', implode(' ', $css));
            }
            $this->html = $document->saveHTML($form);
            if(empty($this->invalid)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Loop through all the elements of a form and validate them if needed.
     *
     * @param DomElement $node The dome element to validate
     */
    protected function validate($node) {
        if($node->nodeName == 'input') {
            $type = $node->getAttribute('type');
            $name = $node->getAttribute('name');
            if(!$type || !$name
              || in_array($type, ['button', 'image', 'reset'])) {
                return;
            }

            $value = isset($_POST[$name]) ? $_POST[$name] : null;
            if($type == 'submit') {
                $this->active = $value == $node->getAttribute('value');
                return;
            }
            if($type == 'radio') {
                if($value == $node->getAttribute('value')) {
                    $node->setAttribute('checked', True);
                } else {
                    $node->removeAttribute('checked');
                }
                return;
            }
            if($type == 'checkbox') {
                if($value) {
                    $node->setAttribute('checked', True);
                } else {
                    if($node->hasAttribute('required')) {
                        $this->invalid[] = $node;
                    }
                    $node->removeAttribute('checked');
                }
                return;
            }

            $node->setAttribute('value', $value);
            if(!$value) {
                if($node->hasAttribute('required')) {
                    $this->invalid[] = $node;
                }
                return;
            }
            if($type == 'number' || $type == 'range') {
                $min = $node->getAttribute('min');
                if($min && $value < $min) {
                    $this->invalid[] = $node;
                }
                $max = $node->getAttribute('max');
                if($max && $value > $max) {
                    $this->invalid[] = $node;
                }
            } else if ($type == 'url') {
                if(!filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->invalid[] = $node;
                }
            } else if ($type == 'email') {
                if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->invalid[] = $node;
                }
            }
            $size = $node->getAttribute('maxlength');
            if($size && strlen($value) > $size) {
                $this->invalid[] = $node;
            }
            $pattern = $node->getAttribute('pattern');
            if($pattern && !preg_match('`'.$pattern.'`', $value)) {
                $this->invalid[] = $node;
            }
        } else if ($node->nodeName == 'select') {
            $name = $node->getAttribute('name');
            $value = isset($_POST[$name]) ? $_POST[$name] : null;
            if($node->getAttribute('required') && !$value) {
                $this->invalid[] = $node;
            }
            foreach($node->childNodes as $n) {
                if($n->nodeName == 'option') {
                    $option = $n->getAttribute('Value');
                    if(!$option) {
                        $option = $n->nodeValue;
                    }
                    if($option == $value) {
                        $n->setAttribute('selected', True);
                    } else {
                        $n->removeAttribute('selected');
                    }
                }
            }
        } else if ($node->nodeName == 'textarea') {
            $name = $node->getAttribute('name');
            $value = isset($_POST[$name]) ? $_POST[$name] : null;
            if($node->getAttribute('required') && !$value) {
                $this->invalid[] = $node;
            }
            $node->nodeValue = $value;
        } else if ($node->childNodes) {
            foreach($node->childNodes as $n) {
                $this->validate($n);
            }
        }
    }
}

}
