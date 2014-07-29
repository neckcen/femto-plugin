<?php

namespace femto\plugin;

/**
 * A plugin for Femto that allows associating a php file with a page.
 *
 * @author Sylvain Didelot
 */
class PHP {
    protected $config;
    protected $is_valid;

    /**
     * Instance the plugin with given configuration.
     * Possible keys:
     *  php_form_validate - whether to validate forms
     *  php_form_error_class - css class to add to incorrect fields
     *
     * @param array $config The website configuration.
     */
    public function __construct($config) {
        $default = array(
            'php_form_validate' => True,
            'php_form_error_class' => 'input-error',
        );
        $this->config = array_merge($default, $config);
    }


    /**
     * Add the php header.
     *
     * @param array $header List of headers.
     */
    public function page_before_read_header(&$header) {
        $header['php'] = null;
    }

    /**
     * Validate forms if enabled, substitute variables if any.
     *
     * @param array $page Femto page.
     */
    public function page_before_parse_content(&$page) {
        if($page['php']) {
            $forms = array();
            if($_SERVER['REQUEST_METHOD'] == 'POST'
              && $this->config['php_form_validate']) {
                $match = array();
                $re = '`<form.*?</form>`si';
                preg_match_all($re, $page['content'], $match, PREG_SET_ORDER);
                foreach($match as $m) {
                    $document = new \DOMDocument();
                    $document->encoding = 'UTF-8';
                    $document->loadHTML($m[0]);
                    $node = $document->getElementsByTagName('form')->item(0);
                    $this->is_valid = True;
                    $this->validate($node);
                    $id = $node->getAttribute('id');
                    if($id) {
                        $forms[$id] = $this->is_valid;
                    }
                    $forms[] = $this->is_valid;
                    $page['content'] = str_replace($m[0],
                      $document->saveHTML($node), $page['content']);
                }
            }
            $vars = $this->include_page($page, $forms);
            if(!empty($vars)) {
                foreach($vars as $key=>$value) {
                    $key = '%'.$key.'%';
                    $page['content'] = str_replace($key, $value, $page['content']);
                }
            }
        }
    }

    /**
     * Include the file set in the php header. Happens in a separate function
     * in order to have a clean environment.
     *
     * @param array $page Femto page.
     * @param array $forms List of validated forms.
     */
    protected function include_page($page, $forms) {
        $config = $this->config;
        $vars = array();
        include(dirname($page['file']).'/'.$page['php']);
        return $vars;
    }

    /**
     * Loop through all the elements of a form and validate them if needed.
     *
     * @param DomElement $node The dome element to validate.
     */
    protected function validate($node) {
        if($node->nodeName == 'input') {
            $type = $node->getAttribute('type');
            $name = $node->getAttribute('name');
            if(!$type || !$name
              || in_array($type, array('submit', 'button', 'image', 'reset'))) {
                return;
            }

            $value = isset($_POST[$name]) ? $_POST[$name] : null;
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
                        $this->invalid($node);
                    }
                    $node->removeAttribute('checked');
                }
                return;
            }

            $node->setAttribute('value', $value);
            if(!$value) {
                if($node->hasAttribute('required')) {
                    $this->invalid($node);
                }
                return;
            }
            if($type == 'number' || $type == 'range') {
                $min = $node->getAttribute('min');
                if($min && $value < $min) {
                    $this->invalid($node);
                }
                $max = $node->getAttribute('max');
                if($max && $value > $max) {
                    $this->invalid($node);
                }
            } else if ($type == 'url') {
                if(!filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->invalid($node);
                }
            } else if ($type == 'email') {
                if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->invalid($node);
                }
            }
            $size = $node->getAttribute('maxlength');
            if($size && strlen($value) > $size) {
                $this->invalid($node);
            }
            $pattern = $node->getAttribute('pattern');
            if($pattern && !preg_match('`'.$pattern.'`', $value)) {
                $this->invalid($node);
            }
        } else if ($node->nodeName == 'select') {
            $name = $node->getAttribute('name');
            $value = isset($_POST[$name]) ? $_POST[$name] : null;
            if($node->getAttribute('required') && !$value) {
                $this->invalid($node);
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
                $this->invalid($node);
            }
            $node->nodeValue = $value;
        } else if ($node->childNodes) {
            foreach($node->childNodes as $n) {
                $this->validate($n);
            }
        }
    }

    /**
     * Mark a node as incorrect and flag the whole form as invalid.
     *
     * @param DomElement $node The dome element to mark.
     */
    protected function invalid($node) {
        $css = explode(' ', $node->getAttribute('class'));
        if(!in_array($this->config['php_form_error_class'], $css)) {
            $css[] = $this->config['php_form_error_class'];
        }
        $node->setAttribute('class', implode(' ', $css));
        $this->is_valid = False;
    }
}
