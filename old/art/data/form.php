<?php
/**
 * utility for easy forms
 *
 * LICENSE: see Art/license.txt
 *
 * @authorÂ Jean-Baptiste VerreyÂ <jeanbaptiste.verrey@gmail.com>
 * @copyright  2010 Jean-Baptiste Verrey
 * @license    http://www.Art.com/license/2.txt
 * @version    $Id$
 * @link       http://www.Art.com/manual/
 * @since      2.0
 */
/**
 * @category   Data
 * @package    Art2
 */
/*
 * @todo token
 */
class Art_Data_Form extends ArrayIterator {
    protected $_objects = array();
    protected $_error = false;
    protected $_requestNames = array();
    protected $_request;
    protected $_label;
    protected $_required = false;
    protected $_url = '';
    protected $_domCss = '';
    protected $_view;
    protected $_parent = false;
    protected $_session;
    protected $_htmlSubmitButtonText = 'ok';
    protected $_javascriptValidatorIcon = true;
    protected static $_helpers = array();
    protected static $_popupError;
    protected static $_addedUpload = false;
    protected $_dontUseViewButton = false;
    public static $viewButtons = ''; // to add beautiful buttons to save / cancel
    // CORE4

    public function __construct($title) {
        $this->_label = $title;
        $this->_requestNames[] = preg_replace('/[^a-zA-Z0-9_]/', '', $title);
    }

    public function dontUseButtonView() {
        $this->_dontUseViewButton = true;
    }

    public static function hasHelper($name) {
        return isset(self::$_helpers[$name]);
    }

    public static function setHelper($name, $object) {
        self::$_helpers[$name] = $object;
    }

    public function __get($name) {
        return self::$_helpers[$name];
    }

    protected function _getRequestName() {
        return implode('_', array_reverse(array_unique($this->_requestNames)));
    }

    public function setLabel($title) {
        $this->_label = $title;
    }

    public function getLabel() {
        return $this->_label;
    }

    public function setRequest(&$request) {
        if (self::hasHelper('upload'))
            self::$_helpers['upload']->setRequest($request);
        $this->_request = &$request;
    }

    public function setParent(Art_Data_Form $parent) {
        $this->_parent = $parent;
        $this->_requestNames = array_merge($this->_requestNames, $this->_parent->_requestNames);
        foreach ($this->_objects as $name => $object)
            if ($object instanceof self)
                $object->setParent($this);
    }

    public function setRequired($bool) {
        $this->_required = $bool;
    }

    public function isRequired() {
        return $this->_required;
    }

    public function setError($error=true) {
        $this->_error = $error;
    }

    public function add($dataOrForm, $name=false, $required=false) {
        if (!$dataOrForm instanceof Art_Data && !$dataOrForm instanceof self)
            throw new Art_Data_Form_Exception('form can receive only Art_Data and Art_Data_Form objects');
        $objectRequestName = $name ? preg_replace('/[^a-zA-Z0-9_]/', '', $name) : count($this->_objects);
        if ($dataOrForm instanceof self) {
            $this->_hasChild = true;
            $dataOrForm->setParent($this);
        } else {
            if ($name)
                $dataOrForm->setLabel($name);
        }
        if ($required)
            $dataOrForm->setRequired($required);
        $this->_objects[$objectRequestName] = $dataOrForm;
        return $dataOrForm;
    }

    public function remove($name) {
        unset($this->_objects[$name]);
    }

    public function offsetGet($name) {
        if (!isset($this->_objects[$name]))
            throw new Art_Form_Exception('Input "' . $name . '" does not exist');
        return $this->_objects[$name];
    }

    public function getObjects() {
        return $this->_objects;
    }

    public function received() {
        $this->_requestName = $this->_getRequestName();
        // session
        if (!isset($this->_session)) {
            $session = Art_Session::useNamespace('form');
            if (!isset($session->{$this->_requestName}))
                $session->{$this->_requestName} = array();
            $this->_session = $session->{$this->_requestName};
        }
        // token
        if (isset($this->_session->token))
            $this->_session->oldToken = $this->_session->token;
        $this->_session->token = md5(uniqid(rand(), true));
        // rest
        $i = 0;
        if (!isset($this->_request))
            $this->setRequest($_REQUEST[$this->_requestName]);
        foreach ($this->_objects as $objectRequestName => $object) {
            if ($object instanceof self) {
                $object->received();
            } else {
                $object->setRequestName($this->_requestName . '[' . $objectRequestName . ']');
                if (isset($this->_request[$objectRequestName])) {
                    if ($object->getType() != 'boolean')
                        $this->_session->{$objectRequestName} = $this->_request[$objectRequestName];
                    $object->setValueForDatabase($this->_request[$objectRequestName]);
                } elseif (isset($this->_session->{$objectRequestName}) && $object->getType() != 'file') {
                    $object->setValueForDatabase($this->_session->{$objectRequestName} instanceof Art_Block ? $this->_session->{$objectRequestName}->toArray() : $this->_session->{$objectRequestName});
                }
            }
        }
        return isset($this->_request);
    }

    public function reset() {
        if (!isset($this->_session)) {
        $this->_requestName = $this->_getRequestName();
            $session = Art_Session::useNamespace('form');
            if (!isset($session->{$this->_requestName}))
                $session->{$this->_requestName} = array();
            $this->_session = $session->{$this->_requestName};
        }
        $this->_session->reset();
        foreach ($this->_objects as $object)
            if ($object instanceof self)
                $object->reset();
    }

    public function isEmpty() {
        $empty = true;
        foreach ($this->_objects as $object) {
            if (!$object->isEmpty()) {
                $empty = false;
                break;
            }
        }
        return $empty;
    }

    public function error() {
        $hasError = false;
        $empty = $this->isEmpty();
        if ($empty) {
            return $this->_required ? 'required' : $this->_error;
        }
        foreach ($this->_objects as $object) {
            // inner form
            if ($object instanceof self) {
                $error = $object->error();
                if ($error)
                    $hasError = $error;
                // inner data
            } else {
                if ($object->error())
                    $hasError = 'data_error_' . $object->getRequestName();
            }
        }
        return $this->_error ? $this->_error : ($hasError ? $hasError : false);
    }

    public function setUrl($url) {
        $this->_url = $url;
    }

    public function setDomCss($class) {
        $this->_domCss = $class;
    }

    public function setView($view) {
        $this->_view = $view;
    }

    public function setSubView($view) {
        foreach ($this->_objects as $object) {
            // inner form
            if ($object instanceof self) {
                $object->setView($view);
            }
        }
    }

    public function toArray() {
        $ret = array();
        foreach ($this->_objects as $name => $obj)
            $ret[$name] = $obj instanceof self ? $obj->toArray() : $obj->getValue();
        return $ret;
    }

    public function output() {
        $viewPath = Art_Configuration::getInstance()->form->viewPath;
        $view = $this->_view ? $viewPath . $this->_view . '.php' : $viewPath . 'default.php';
        include($view);
    }

    public function __toString() {
        ob_start();
        try {
            $this->output();
        } catch (Exception $e) {
            if (Art_Debug::isActivated()
            )
                pre($e);
            echo '<!-- Art_Data_Form::__toString() error -->';
        }
        return ob_get_clean();
    }

    public function useSubmitButton($bool) {
        $this->_useSubmitButton = $bool;
    }

    public function setJavascriptValidatorIcon($bool) {
        $this->_javascriptValidatorIcon = $bool;
    }

    public static function javascriptValidatorGeneral() {
        echo '<script>$translations={"required":"' . Art_I18n::getInstance()->get('required') . '"}';
        require(dirname(__FILE__) . '/form.js');
        echo '</script>';
    }

    public function javascriptValidator() {
        if (isset(self::$_popupError)
        )
            return;
        self::javascriptValidatorGeneral();
    }

    public function setHtmlSubmitButtonText($text) {
        $this->_htmlSubmitButtonText = $text;
    }

    // !CORE4

    public function htmlStart() {
        $rqName = $this->_getRequestName();
        if ($this->_parent)
            return '<fieldset isrequired="' . ($this->_required ? 'isrequired' : 'false') . '" class="' . $this->_domCss . '">';
        return '<form iconvalidator="' . ($this->_javascriptValidatorIcon ? 1 : 0) . '" method="post" class="' . $this->_domCss . '" name="' . $rqName . '"  enctype="multipart/form-data" id="' . $rqName . '" action="' . $this->_url . '">';
    }

    public function htmlObject($object) {
        return '<div class="row">' . $this->htmlInputLabel($object) . $this->htmlInput($object) . $this->htmlInputError($object) . '</div>';
    }

    public function htmlInput(Art_Data $object, $tags=true) {
        if ($this->_domCss
        )
            $object->css = $this->_domCss;
        $string = $object->input() . '&nbsp;';
        return $tags ? '<div class="input">' . $string . '</div>' : $string;
    }

    public function htmlInputLabel($object) {
        return '<div class="label">' . $object->getLabel() . ($object->isRequired() ? '<span class="required">*</span>' : '') . '&nbsp;</div>';
    }

    public function htmlInputError($object) {
        return '<div class="error">' . $object->getError() . '&nbsp;</div>';
    }

    public function htmlStop() {
        return $this->_parent ? '</fieldset>' : '<input type="hidden" class="' . $this->_domCss . '" name="' . $this->_getRequestName() . '[token]" value="' . $this->_session->token . '" />' . ($this->_dontUseViewButton ? '' : self::$viewButtons) . '</form>';
    }

    public function htmlSubmitButton($text=false, $id='submit', $css=false) {
        if (!$text)
            $text = $this->_htmlSubmitButtonText;
        if ($this->_parent
        )
            return '';
        $rqName = $this->_getRequestName();
        return '<input type="submit" class="submit ' . $this->_domCss . ' ' . $css . '" name="' . $rqName . '[' . $id . ']" id="' . ($this->_parent ? $this->_parent->_getRequestName() . '[' . $rqName . '][' . $id . ']' : $rqName . '[' . $id . ']' ) . '" value="' . $text . '"/>';
    }
}
?>