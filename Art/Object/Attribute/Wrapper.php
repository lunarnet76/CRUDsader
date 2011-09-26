<?php
namespace Art\Object\Attribute {
    abstract class Wrapper {

        protected $_options;
        
        public function __construct($options=array()){
            $this->_options=$options;
        }
        
        abstract function formatForDatabase($value);

        abstract function formatFromDatabase($value);

        abstract function isValid($value);

        abstract function isEmpty($value);

        abstract function HTMLInput($value, $id, $htmlAttributes);

        abstract function javascriptValidator();

        abstract function generateRandom();
    }
}