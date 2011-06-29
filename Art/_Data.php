<?php
namespace Art {
    class Data extends \Art\Form\Component {
        protected $_value;

        public function receive($data=false) {
            if ($request === null)
                return false;
            $this->_value = $request;

            return true;
        }

        public function isEmpty() {
            return false;
        }

        public function error() {
            return false;
        }

        public function toArray() {
            
        }

        public function toHTML() {
            return 'html';
        }

        public function __toString() {
            return 'form';
        }
    }
}