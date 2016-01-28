<?php
/**
 * Useful functions and classes available to PHP scripts.
 *
 */
namespace femto\plugin\php\util;

/**
 * Throwing this exception triggers an error page.
 */
class Exception extends \Exception {}

/**
 * Return the page associated with the url.
 *
 * @see \femto\Page::resolve()
 *
 * @param string $url The url to resolve
 * @return Page Femto page, null if not found
 */
function page($url) {
    return \femto\Page::resolve($url);
}

/**
 * Return the directory associated with the url.
 *
 * @see \femto\Directory::resolve()
 *
 * @param string $url The url to list
 * @return Directory Femto directory
 */
function directory($url) {
    return \femto\Directory::resolve($url);
}

/**
 * Redirect to a different url.
 *
 * Usage:
 *
 * // redirect to an arbitrary url
 * redirect('http://php.net');
 *
 * // use a different redirect code (default 303)
 * redirect('http://php.net', 301);
 *
 * // redirect to a different femto page
 * redirect(page('/404'));
 *
 * @see http://racksburg.com/choosing-an-http-status-code/
 *
 * @param string $to Url to redirect to
 * @param int $code HTTP code to send
 */
function redirect($to, $code=303) {
    header('Location: '.$to, true, $code);
    exit();
}

/**
 * Escape a string for use in HTML code.
 *
 * @param string $string unescaped string
 * @return string escaped string
 */
function escape() {
    $string = implode('', func_get_args());
    return \femto\escape($string);
}

/**
 * Build a query string based on the current one.
 *
 * Usage:
 *
 * // the current query string
 * qs();
 *
 * // selected variable from current query string
 * // use * to copy all current variables
 * qs('variable_selected');
 *
 * // new variable
 * qs(['new_variable'=>'new_value']);
 *
 * // combined
 * qs(['new_variable'=>'new_value','variable_selected']);
 *
 * @param array $select variables to keep or add
 * @return string query string
 */
function qs($select='*') {
    if(!is_array($select)) $select = [$select];
    $qs = [];
    foreach($select as $key => $value) {
        if(is_string($key)) {
            $qs[] = $key.'='.urlencode($value);
        } else if ($value == '*') {
            $qs[] = $_SERVER['QUERY_STRING'];
        } else if (isset($_GET[$value])) {
            $qs[] = $value.'='.urlencode($_GET[$value]);
        }
    }
    return empty($qs) ? '' : '?'.implode('&', $qs);
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
                    $option = $n->getAttribute('value');
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
