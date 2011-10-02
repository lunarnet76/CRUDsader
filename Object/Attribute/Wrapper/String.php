<?php
namespace CRUDsader\Object\Attribute\Wrapper {
    class String extends \CRUDsader\Object\Attribute\Wrapper {

        public function formatForDatabase($value) {
            return filter_var($value,FILTER_SANITIZE_STRING);
        }

        public function formatFromDatabase($value) {
            return $value;
        }

        public function isValid($value) {
            if(strlen(filter_var($value,FILTER_SANITIZE_STRING))>$this->_options['length'])return 'string_error_too_long_'.$this->_options['length'];
            return true;
        }

        public function isEmpty($value) {
            return empty($value);
        }

        public function HTMLInput($value, $id, $htmlAttributes) {
            return '<input type="text" name="' . $id . '" value="' . $value . '" ' . $htmlAttributes . '/>';
        }

        public function javascriptValidator() {
            return '';
        }

        public function generateRandom() {
            return base_convert(rand(10e16, 10e20), 10, 36);
        }
    }
}
