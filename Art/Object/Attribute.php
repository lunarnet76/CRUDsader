<?php
namespace Art\Object {
    class Attribute extends \Art\Form\Component {
        protected $_value;

        public function __construct($name, $wrapper, $options=array()) {
            $this->_name = $name;
            $this->_wrapper = new $wrapper($options);
        }

        public function error() {
            $valid = $this->_wrapper->isValid($this->_value);
            $this->_error = $valid === true ? false : ($valid === false ? true : $valid);
            return $this->_error;
        }

        public function isEmpty() {
            return $this->_wrapper->isEmpty($this->_value);
        }

        public function toArray() {
            return $this->_value;
        }

        public function setValue($value, $mandatory=false) {
            $this->_error = false;
            $empty = $this->_wrapper->isEmpty($value);
            if ($empty && $mandatory) {
                $this->_error = 'required';
            }
            $valid = $this->_wrapper->isValid($value);
            if ($valid===true) {
                $this->_value = $empty ? new \Art\Expression\Void : $value;
            }else
                $this->_error = $valid===false?true:$valid;
            return $this->_error===false?true:false;
        }

        /**
         * receive from a form
         * @param type $data 
         */
        public function receive($data=false) {
            return $this->_setValue($data);
        }

        /**
         * when writing object from database
         * @param type $value 
         */
        public function setValueFromDatabase($value) {
            $this->_setValue($this->_wrapper->formatFromDatabase($value));
        }

        public function getValueForDatabase() {
            return $this->_wrapper->formatForDatabase($this->_value);
        }

        protected function _setValue($value) {
            $this->_value = $this->_wrapper->isEmpty($value) ? new \Art\Expression\Void : $value;
        }

        public function getValue() {
            return $this->_value;
        }

        public function toHTML() {
            $this->setHTMLAttribute('validator', $this->_wrapper->javascriptValidator());
            return $this->_wrapper->HTMLInput($this->_value instanceof \Art\Expression\Void?'':$this->_value, $this->_htmlAttributes['id'], $this->getHTMLAttributes());
        }
    }
}