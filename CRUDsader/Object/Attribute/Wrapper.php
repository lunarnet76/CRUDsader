<?php
namespace CRUDsader\Object\Attribute {
    abstract class Wrapper {

        protected $_options;
        
        public function __construct($options=array()){
            $this->_options=$options;
        }
        
        public function formatForDatabase($value){
            return filter_var($value,FILTER_SANITIZE_STRING);;
        }

        function formatFromDatabase($value){
            return $value;
        }

        function isValid($value){
            return true;
        }

        public function isEmpty($value){
            return \CRUDsader\Expression::isEmpty($value);
        }

        abstract function HTMLInput($value, $id, $htmlAttributes);

        public function javascriptValidator() {
            return '';
        }

        public function generateRandom() {
            return base_convert(rand(10e16, 10e20), 10, 36);
        }
        
        public function getValue($value){
            return $value;
        }
    }
}