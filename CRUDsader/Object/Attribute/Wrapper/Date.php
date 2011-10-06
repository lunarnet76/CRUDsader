<?php
namespace CRUDsader\Object\Attribute\Wrapper {
    class Date extends \CRUDsader\Object\Attribute\Wrapper {

        public function formatForDatabase($value) {
            $x = explode('/', $value);
            switch (count($x)) {
                case 1:
                    return $x[0] . '-00-00';
                    break;
                case 2:
                    return $x[1] . '-' . $x[0] . '-00';
                    break;
                default:
                    return $x[2] . '-' . $x[1] . '-' . $x[0];
            }
        }

        public function formatFromDatabase($value) {
            $x = explode('-', $value);
            return(!empty($x[2]) && $x[2] > 0 ? $x[2] . '/' : '') . (!empty($x[1]) && $x[1] > 0 ? $x[1] . '/' : '') . $x[0];
        }

        public function isValid($value) {
            return preg_match('|[0-9]{2}(/[0-9]{2})?(/[0-9]{4})?|', $value) ? true : 'invalid';
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
