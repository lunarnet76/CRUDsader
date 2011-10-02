<?php
namespace CRUDsader\Object\Attribute\Wrapper {
    class Text extends \CRUDsader\Object\Attribute\Wrapper {

        public function formatForDatabase($value) {
            return nl2br(filter_var($value,FILTER_SANITIZE_STRING));
        }

        public function formatFromDatabase($value) {
            return $value;
        }

        public function isValid($value) {
            return true;
        }
        public function getValue($value){
            return nl2br($value);
        }
        

        public function isEmpty($value) {
            return empty($value);
        }

        public function HTMLInput($value, $id, $htmlAttributes) {
            return '<textarea name="' . $id . '" ' . $htmlAttributes . '>'.$value.'</textarea>';
        }

        public function javascriptValidator() {
            return '';
        }

        public function generateRandom() {
            return base_convert(rand(10e16, 10e20), 10, 36);
        }
    }
}
