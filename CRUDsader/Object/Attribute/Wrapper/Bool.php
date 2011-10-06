<?php
namespace CRUDsader\Object\Attribute\Wrapper {
    class Bool extends \CRUDsader\Object\Attribute\Wrapper {

        public function __construct(\CRUDsader\Object\Attribute $attribute,$options=array()){
            $this->_options=$options;
            $attribute->setParameter('isCheckbox',true);
        }
        
        public function formatForDatabase($value) {
            return $value=='yes'?1:0;
        }

        public function formatFromDatabase($value) {
            return $value==1;
        }

        public function isValid($value) {
            return true;
        }

        public function isEmpty($value) {
            return false;
        }

        public function HTMLInput($value, $id, $htmlAttributes) {
            return '<input value="yes" '.($value?'checked="checked"':'').' type="checkbox" name="' . $id . '" ' . $htmlAttributes . '/>';
        }

        public function javascriptValidator() {
            return '';
        }

        public function generateRandom() {
            return base_convert(rand(10e16, 10e20), 10, 36);
        }
    }
}
