<?php
namespace Art\Object {
    class Attribute extends \Art\Form\Component {

        public function __construct($name, $wrapper, $options=false) {
            $this->_name = $name;
            $this->_wrapper = $wrapper;
            $this->_options = $options;
        }

        public function error() {
            $valid=call_user_func_array(array($this->_wrapper, 'isValid'), array($this->_value, $this->_options));
            $this->_error=$valid===true?false:($valid===false?true:$valid);
            return $this->_error;
        }

        public function isEmpty() {
            return call_user_func_array(array($this->_wrapper, 'isEmpty'), array($this->_value, $this->_options));
        }

        public function toArray() {
            
        }

        /**
         * receive with a form
         * @param type $data 
         */
        public function receive($data=false) {
            $this->setValue($data);
        }

        /**
         * when writing object from database
         * @param type $value 
         */
        public function setValueFromDatabase($value) {
            $this->_setValue(call_user_func_array(array($this->_wrapper, 'formatFromDatabase'), array($value, $this->_options)));
        }

        /**
         * when writing object to database
         * @param type $value 
         */
        public function getValueForDatabase($value) {
            return call_user_func_array(array($this->_wrapper, 'formatForDatabase'), array($this->_value, $this->_options));
        }

        protected function setValue($value) {
            $this->_value = call_user_func_array(array($this->_wrapper, 'isEmpty'), array($value, $this->_options)) ? new \Art\Expression\Void : $value;
        }

        public function getValue() {
            return $this->_value;
        }

        public function toHTML() {
            $this->setHTMLAttribute('validator', call_user_func_array(array($this->_wrapper, 'javascriptValidator'), array($this->_options)));
            return call_user_func_array(array($this->_wrapper, 'HTMLInput'), array($this->_value, $this->_id, $this->_options, $this->getHTMLAttributes()));
        }
    }
}