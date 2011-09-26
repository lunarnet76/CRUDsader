<?php
namespace Art\Object {
    class Attribute extends \Art\Form\Component {
        
        public function __construct($name, $wrapper, $options=array()) {
            parent::__construct();
            $this->_name = $name;
            $this->_wrapper = new $wrapper($options);
        }
        
        /**
         * return true if valid, string or false otherwise
         * @return type 
         */
        protected function _inputValid() {
            return $this->_wrapper->isValid($this->_inputValue);
        }

        public function inputEmpty() {
            return $this->_inputValue instanceof \Art\Expression\Nil || $this->_wrapper->isEmpty($this->_inputValue);
        }

        /**
         * when writing object from database
         * @param type $value 
         */
        public function setValueFromDatabase($value) {
            if(empty($value) || $this->_wrapper->isEmpty($value))
                $this->_inputValue=new \Art\Expression\Nil();
            else
                $this->_inputValue = $this->_wrapper->formatFromDatabase($value);
        }

        public function getValueForDatabase() {
            return $this->_inputValue instanceof \Art\Expression\Nil || $this->inputEmpty()?$this->_inputValue:$this->_wrapper->formatForDatabase($this->_inputValue);
        }


        public function getValue() {
            return $this->_inputValue;
        }

        public function toHTML() {
            $this->setHTMLAttribute('validator', $this->_wrapper->javascriptValidator());
            return $this->_wrapper->HTMLInput($this->_inputValue instanceof \Art\Expression\Nil?'':$this->_inputValue, $this->_htmlAttributes['id'], $this->getHTMLAttributesToHtml());
        }
    }
}