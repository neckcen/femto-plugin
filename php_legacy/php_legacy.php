<?php

namespace femto\plugin\php_legacy;

/**
 * A plugin for Femto that allows associating a php file with a page.
 *
 * @author Sylvain Didelot
 */

class local  {
    static public $valid;
}

/**
 * Instance the plugin with given configuration.
 * Possible keys:
 *  php_form_validate - whether to validate forms
 *  php_form_error_class - css class to add to incorrect fields
 *
 * @param array $config The website configuration.
 */
function config(&$config) {
    $default = [
        'php_legacy_form_validate' => True,
        'php_legacy_form_error_class' => 'input-error',
    ];
    \femto\Femto::$config = \femto\Femto::$config + $default;

    \femto\Page::$header['php'] = null;
}


/**
 * Validate forms if enabled, substitute variables if any.
 *
 * @param array $page Femto page.
 */
function page_content_before($page) {
    if(!$page['php']) return;

    $forms = [];
    if($_SERVER['REQUEST_METHOD'] == 'POST'
      && \femto\Femto::$config['php_legacy_form_validate']) {
        $match = [];
        $re = '`<form.*?</form>`si';
        preg_match_all($re, $page['content'], $match, PREG_SET_ORDER);
        foreach($match as $m) {
            $document = new \DOMDocument();
            $document->encoding = 'UTF-8';
            $document->loadHTML('<?xml encoding="utf-8" ?>'.$m[0]);
            $node = $document->getElementsByTagName('form')->item(0);
            local::$valid = True;
            validate($node);
            $id = $node->getAttribute('id');
            if($id) {
                $forms[$id] = local::$valid;
            }
            $forms[] = local::$valid;
            $page['content'] = str_replace($m[0],
              $document->saveHTML($node), $page['content']);
        }
    }
    $vars = [];
    $return = include_page($page, $vars, $forms);
    if(!is_array($return)) {
        $return = [$return];
    }
    if($return[0] == 'redirect') {
        $to = isset($return[1]) ? $return[1] : '%self%';
        $code = isset($return[2]) ? (int) $return[2] : 303;
        $seek = [
            '%self_qsa%',
            '%self%',
            '%dir%',
            '%base%',
        ];
        $replace = [
            \femto\Femto::$config['base_url'].$page['url'].'?'.$_SERVER['QUERY_STRING'],
            \femto\Femto::$config['base_url'].$page['url'],
            \femto\Femto::$config['base_url'].$page['directory']['url'],
            \femto\Femto::$config['base_url'],
        ];
        $to = str_replace($seek, $replace, $to);
        header('Location: '.$to, true, $code);
        exit();

    } else if($return[0] == 'error') {
        header('Internal Server Error', true, 500);
        if(count($return) > 2) {
            $page['title'] = $return[1];
            $page['content'] = $return[2];
        } else if(count($return) > 1) {
            $page['title'] = 'Error 500';
            $page['content'] = $return[1];
        } else {
            $page['title'] = 'Error 500';
            $page['content'] = 'Internal Server Error';
        }

    } else if(!empty($vars)) {
        foreach($vars as $key=>$value) {
            $key = ['%'.$key.'%', 'php="'.$key.'"'];
            $page['content'] = str_replace($key, $value, $page['content']);
        }
    }
}

/**
 * Include the file set in the php header. Happens in a separate function
 * in order to have a clean environment.
 *
 * @param array $page Femto page.
 * @param array $vars Variables to be substituted in page's content.
 * @param array $forms List of validated forms.
 */
function include_page($page, &$vars, $forms) {
    $config = \femto\Femto::$config;
    return include(dirname($page['file']).'/'.$page['php']);
}

/**
 * Loop through all the elements of a form and validate them if needed.
 *
 * @param DomElement $node The dome element to validate.
 */
function validate($node) {
    if($node->nodeName == 'input') {
        $type = $node->getAttribute('type');
        $name = $node->getAttribute('name');
        if(!$type || !$name
          || in_array($type, ['submit', 'button', 'image', 'reset'])) {
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
                    invalid($node);
                }
                $node->removeAttribute('checked');
            }
            return;
        }

        $node->setAttribute('value', $value);
        if(!$value) {
            if($node->hasAttribute('required')) {
                invalid($node);
            }
            return;
        }
        if($type == 'number' || $type == 'range') {
            $min = $node->getAttribute('min');
            if($min && $value < $min) {
                invalid($node);
            }
            $max = $node->getAttribute('max');
            if($max && $value > $max) {
                invalid($node);
            }
        } else if ($type == 'url') {
            if(!filter_var($value, FILTER_VALIDATE_URL)) {
                invalid($node);
            }
        } else if ($type == 'email') {
            if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                invalid($node);
            }
        }
        $size = $node->getAttribute('maxlength');
        if($size && strlen($value) > $size) {
            invalid($node);
        }
        $pattern = $node->getAttribute('pattern');
        if($pattern && !preg_match('`'.$pattern.'`', $value)) {
            invalid($node);
        }
    } else if ($node->nodeName == 'select') {
        $name = $node->getAttribute('name');
        $value = isset($_POST[$name]) ? $_POST[$name] : null;
        if($node->getAttribute('required') && !$value) {
            invalid($node);
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
            invalid($node);
        }
        $node->nodeValue = $value;
    } else if ($node->childNodes) {
        foreach($node->childNodes as $n) {
            validate($n);
        }
    }
}

/**
 * Mark a node as incorrect and flag the whole form as invalid.
 *
 * @param DomElement $node The dome element to mark.
 */
function invalid($node) {
    $css = explode(' ', $node->getAttribute('class'));
    if(!in_array(\femto\Femto::$config['php_legacy_form_error_class'], $css)) {
        $css[] = \femto\Femto::$config['php_legacy_form_error_class'];
    }
    $node->setAttribute('class', implode(' ', $css));
    local::$valid = False;
}
