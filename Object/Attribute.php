<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object {
    class Attribute extends \CRUDsader\Form\Component {
        protected $_name;
	
	/**
         * list fields to include in array
         * @var string
         */
        protected $_toArray = array(
	    '_name','_parameters','_htmlAttributes','_htmlLabel','_value','_valueDefault','_options'
	);

        public function __construct($name=false, $options=array()) {
            parent::__construct($options);
            $this->_name = $name;
        }

        /**
         * return true if valid, string or false otherwise
         * @return type 
         */
        protected function _inputValid() {
            return true;
        }

        /**
         * @return bool
         */
        public function inputEmpty() {
            return !isset($this->_value);
        }

        /**
         * when writing object from database
         * @param type $value
         */
        public function setValueFromDatabase($value) {
            if (!isset($value)){
                $this->_value = $this->_valueDefault;
	    }else
                $this->_value = $value;
        }

        public function getValueForDatabase() {
            return $this->_value;
        }

        public function getValue() {
            return $this->inputEmpty() ? $this->_valueDefault : $this->_value;
        }
		
	public function toHumanReadable(){
		$v = $this->getValue();
		return isset($v)?$v:'';
	}

        public function toHtml() {
            $this->_htmlAttributes['validator'] = $this->javascriptValidator();
            return parent::toHtml();
        }

        public function javascriptValidator() {
            return '';
        }

        public function generateRandom() {
            return base_convert(rand(10e16, 10e20), 10, 36);
        }
	
        // it's an input so there is no NULL, only "", null here means that NO value was received
	public function inputReceive($data=null) {
            if(!empty($data))
                $this->_value =  $data;
            else
                $this->_value = null;
            if($data!==null)$this->_inputReceived = true;
            $this->notify();
        }
    }
}