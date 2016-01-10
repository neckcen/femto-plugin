<?php
// functions and classes available to PHP scripts
namespace femto\plugin\PHP;

/**
 * Proxy to \femto\page()
 *
 * @see \femto\page()
 *
 * @param string $url The url to resolve
 * @return array Femto page, null if not found
 */
function page($url) {
    return \femto\page($url);
}

/**
 * Proxy to \femto\directory()
 *
 * @see \femto\directory()
 *
 * @param string $url The url to list
 * @param string $sort Sorting criteria
 * @param string $order Sorting order
 * @return array List of Femto pages with content removed
 */
function directory($url, $sort='alpha', $order='asc') {
    return \femto\directory($url, $sort, $order);
}

/**
 * Redirect to a different page or url. Optionally append (part of) the query
 * string.
 *
 * Usage:
 *
 * // redirect to a different femto page
 * redirect(page('/page'));
 *
 * // redirect to an arbitrary url
 * redirect('http://php.net');
 *
 * // use a different redirect code
 * redirect('http://php.net', 301);
 *
 * // append the entire query string
 * redirect('http://php.net', 301, True);
 *
 * // append specific query string ($_GET) variable
 * redirect('http://php.net', 301, ['variable1']);
 *
 * // append new query string variable
 * redirect('http://php.net', 301, ['variable1'=>'value1']);
 *
 * // combine both previous examples
 * redirect('http://php.net', 301, ['existing_variable', 'variable1'=>'value1']);
 *
 * @see http://racksburg.com/choosing-an-http-status-code/
 * @see page()
 *
 * @param mixed $page Femto page or url to redirect to.
 * @param int $code HTTP code to send
 * @param mixed $qsa True to append the entire query string or an array of keys
 */
function redirect($to, $code=303, $qsa=null) {
    // redirect to a different femto page
    if(is_array($to) && isset($to['url'])) {
        $to = \femto\_::$config['base_url'].$to['url'];
        // append the query string
        if($qsa) {
            $to .= '?';
            if(is_array($qsa)) {
                foreach($qsa as $key => $value) {
                    if(is_string($key)) {
                        $to .= $key.'='.urlencode($value).'&';
                    } else if (isset($_GET[$value])) {
                        $to .= $value.'='.urlencode($_GET[$value]).'&';
                    }
                }
                $to = substr($to, 0, -1);
            } else {
                $to .= $_SERVER['QUERY_STRING'];
            }
        }
    }
    header('Location: '.$to, true, $code);
    exit();
}

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
     * Possible values for radio elements.
     *
     * @var array
     */
    protected $radio;

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
        $this->radio = [];
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
        foreach($this->radio as $name => $info) {
            if(isset($_POST[$name])) {
                if(!in_array($_POST[$name], $info[1])) {
                    $this->invalid = $info[0] + $this->invalid;
                }
            }
        }
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

            // multiple forms support
            if($type == 'submit') {
                $this->active = $value == $node->getAttribute('value');
                return;
            }
            if($type == 'radio') {
                if(!isset($this->radio[$name])) {
                    $this->radio[$name] = [[],[]];
                }
                $option = $node->getAttribute('value');
                $this->radio[$name][0][] = $node;
                $this->radio[$name][1][] = $option;
                if($value == $option) {
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

        // select element
        } else if ($node->nodeName == 'select') {
            $name = $node->getAttribute('name');
            $value = isset($_POST[$name]) ? $_POST[$name] : null;
            // check if required
            if($node->getAttribute('required') && !$value) {
                $this->invalid[] = $node;
            }
            // persist
            $options = [];
            foreach($node->childNodes as $n) {
                if($n->nodeName == 'option') {
                    $option = $n->getAttribute('Value');
                    if(!$option) {
                        $option = $n->nodeValue;
                    }
                    $options[] = $option;
                    if($option == $value) {
                        $n->setAttribute('selected', True);
                    } else {
                        $n->removeAttribute('selected');
                    }
                }
            }
            // if value is set, it must be an existing one
            if($value && !in_array($value, $options)) {
                $this->invalid[] = $node;
            }

        // textarea
        } else if ($node->nodeName == 'textarea') {
            $name = $node->getAttribute('name');
            $value = isset($_POST[$name]) ? $_POST[$name] : null;
            if($node->getAttribute('required') && !$value) {
                $this->invalid[] = $node;
            }
            $node->nodeValue = $value;

        // loop through non-form nodes
        } else if ($node->childNodes) {
            foreach($node->childNodes as $n) {
                $this->validate($n);
            }
        }
    }
}
