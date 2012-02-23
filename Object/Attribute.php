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
            return \CRUDsader\Expression::isEmpty($this->_inputValue);
        }

        /**
         * when writing object from database
         * @param type $value
         */
        public function setValueFromDatabase($value) {
            if (\CRUDsader\Expression::isEmpty($value)){
                $this->_inputValue = $this->_inputValueDefault;
	    }else
                $this->_inputValue = $value;
        }

        public function getValueForDatabase() {
            return $this->inputEmpty() ? new \CRUDsader\Expression\Nil : $this->_inputValue;
        }

        public function getValue() {
            return $this->inputEmpty() ? $this->_inputValueDefault : $this->_inputValue;
        }

        public function toHTML() {
            $this->_htmlAttributes['validator'] = $this->javascriptValidator();
            return parent::toHtml();
        }

        public function javascriptValidator() {
            return '';
        }

        public function generateRandom() {
            return base_convert(rand(10e16, 10e20), 10, 36);
        }
    }
}