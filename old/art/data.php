<?php

/**
 * wrapper for format,generator,input,validator and data view
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
class Art_Data {

    protected $_type; // string,float,listing
    protected $_default; // if provided, is neither coming from the database neither from a form
    protected $_value; // inside value
    protected $_error = false;
    protected $_iconValidator = true;
    protected $_label;
    protected $_css = '';
    protected $_instanceId;
    protected $_requestName; // link to a form
    protected $_required = false;
    protected $_options = array();
    protected $_specify = array();
 // specify subtype, like another view or another input
    protected $_input = array();
    protected $_observer;
    protected $_observerCallbackIndex;
    protected static $_configuration;
    protected static $s=0;
    protected static $_instanceCount=0;
    protected static $_paths = array();


    public function __construct($type=false, array $options=array(), array $specify=array(), $observer=NULL,$observerCallbackIndex=NULL) {
        $this->_specify = $specify;
        $this->_type = $type !== false ? $type : 'default';
        $this->_options = $options;
        if (isset($this->_options['default'])){
            $this->_value =$this->_options['default'];
             $this->_default =$this->_options['default'];
        }
        if (!isset(self::$_configuration)) {
            self::$_configuration = Art_Configuration::getInstance()->data;
            self::$_paths = array_merge(self::$_paths,self::$_configuration->paths->toArray());
        }
        $this->_observer = $observer;
        $this->_observerCallbackIndex = $observerCallbackIndex;
        $this->_instanceId=self::$_instanceCount++;
    }

    public function setIconValidator($bool){
        $this->_iconValidator=$bool;
    }

    public function getType() {
        return $this->_type;
    }

    public function getValue() {
        return $this->_value;
    }

    public function getError() {
        return $this->_error;
    }

    public function setError() {
        return $this->_error;
    }

    public function getLabel() {
        return $this->_label;
    }

    public function setLabel($string) {
        return $this->_label = $string;
    }

    public function getCss() {
        return $this->_css;
    }

    public function setCss($css) {
        $this->_css=$css;
    }

    public function getRequestName() {
        return $this->_requestName;
    }

    public function setRequestName($string) {
        return $this->_requestName = $string;
    }

    public static function registerPath($namespace, $path) {
        self::$_paths[$namespace] = $path;
    }

    public static function unregisterPath($namespace) {
        unset(self::$_paths[$namespace]);
    }

    public function setOption($var, $value) {
        $this->_options[$var] = $value;
    }

    public function setOptions(array $options) {
        $this->_options= $options;
    }

    public function error() {
        if ($this->isEmpty()){
            if ($this->_required)
                $this->_error = Art_I18n::getInstance ()->get('required');
        }else {
                $validity = $this->_load('Validator', 'isValid', $this->_value);
                if ($validity !== true)
                    $this->_error = $validity;
            }
        return $this->_error;
    }

    public function javascriptValidator() {
        return $this->_load('Validator', 'javascriptValid');
    }

    public function isEmpty() {
        if ($this->_value instanceof Art_Database_Expression && $this->_value->get() == 'NULL')return true;
        return $this->_load('Validator', 'isEmpty', $this->_value);
    }

    public function isRequired() {
        return $this->_required;
    }

    public function setRequired($bool=true) {
        $this->_required = $bool;
    }

    public function generate() {
        $this->_value = $this->_load('Generator', 'generate', $this->_value);
    }

    public function setValueForDatabase($value) {
        if($value instanceof Art_Block){
            $val=$value->toArray();
            if(count($val)!=1)
                $value=$val;
        }
        if ($value instanceof Art_Database_Expression)
            $this->_changeValue($value);
        else
            $this->_changeValue($this->_load('Format', 'formatForDatabase', $value));
    }

    public function setValueFromDatabase($value) {
        if ($value instanceof Art_Database_Expression)
            $this->_changeValue($value);
        else
            $this->_changeValue($this->_load('Format', 'formatFromDatabase', $value));
    }

    protected function _changeValue($newValue){
        $this->_value=$newValue;
        if (isset($this->_observer))
            $this->_observer->notify($this->_observerCallbackIndex,$this->isEmpty(),$this->isRequired());
    }

    public function getValueForDatabase() {
        if ($this->_value instanceof Art_Database_Expression)return $this->_value;
        return $this->_load('Format', 'formatForDatabase', $this->_value);
    }

    public function getValueFromDatabase() {
        if ($this->_value instanceof Art_Database_Expression
            )return $this->_value;
        return $this->_load('Format', 'formatFromDatabase', $this->_value);
    }

    public function input() {
        $this->_inputValue=$this->getValueFromDatabase();
        ob_start();
        $this->_view('input');
        return ob_get_clean();
    }

    public function __toString() {
        ob_start();
        $this->_view('view');
        return ob_get_clean();
    }

    /**
     * @todo rempve the Art_Class::autoload by fixing the error
     * @param <type> $class
     * @param <type> $function
     * @param <type> $value
     * @return <type>
     */
    public function _load($class, $function, $value=null) {
        $specify = ucfirst(isset($this->_specify[$class]) && $this->_specify[$class] != 'default' ? $this->_specify[$class] : $this->_type);
        if (Art_Class::isLoadable('Data_' . $class . '_' . $specify))
            $class = 'Data_' . $class . '_' . $specify;
        elseif (Art_Class::isLoadable('Art_Data_' . $class . '_' . $specify))
            $class = 'Art_Data_' . $class . '_' . $specify;
        else
            $class='Art_Data_' . $class . '_Default';
       // Art_Class::autoload($class);// NOT TO BE REMOVED, without it apache would cause a segmentation fault, i don't know why
        if (method_exists($class, $function))
            $sp = $specify;
        else
            $sp='default';
        return call_user_func_array(array($class, $function), array($value, $this->_options));
    }

    public function _view($type) {
        foreach (self::$_paths as $name => $path)
            if (file_exists($path . $type . '/' . $this->_type . '.php')){
                $file = $path . $type . '/' . $this->_type;
                break;
            }
        if (!isset($file))
            foreach (self::$_paths as $name => $path)
                if (file_exists($path . $type . '/default.php'))
                    $file = $path . $type . '/default';
        if (!isset($file))
            throw new Art_Data_Exception('data view not found for type "' . $type . '"');
        require($file . '.php');
    }

}
?>
