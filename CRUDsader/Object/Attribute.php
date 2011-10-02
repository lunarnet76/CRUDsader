<?php
namespace CRUDsader\Object {
    class Attribute extends \CRUDsader\Form\Component {
        
        public function __construct($name, $wrapper, $options=array()) {
            parent::__construct();
            $this->_name = $name;
            $this->_wrapper = new $wrapper($options);
        }
        
        public function getWrapper(){
            return $this->_wrapper;
        }
        
        /**
         * return true if valid, string or false otherwise
         * @return type 
         */
        protected function _inputValid() {
            return $this->_wrapper->isValid($this->_inputValue);
        }

        public function inputEmpty() {
            return $this->_inputValue instanceof \CRUDsader\Expression\Nil || $this->_wrapper->isEmpty($this->_inputValue);
        }

        /**
         * when writing object from database
         * @param type $value 
         */
        public function setValueFromDatabase($value) {
            if(empty($value) || $this->_wrapper->isEmpty($value))
                $this->_inputValue=new \CRUDsader\Expression\Nil();
            else{
                $this->_inputValue = $this->_wrapper->formatFromDatabase($value);
            }
        }

        public function getValueForDatabase() {
            return $this->_inputValue instanceof \CRUDsader\Expression\Nil || $this->inputEmpty()?$this->_inputValue:$this->_wrapper->formatForDatabase($this->_inputValue);
        }


        public function getValue() {
            return $this->_wrapper->getValue($this->_inputValue);
        }

        public function toHTML() {
            $this->setHTMLAttribute('validator', $this->_wrapper->javascriptValidator());
            return $this->_wrapper->HTMLInput($this->_inputValue instanceof \CRUDsader\Expression\Nil?'':$this->_inputValue, $this->_htmlAttributes['id'], $this->getHTMLAttributesToHtml());
        }
    }
}