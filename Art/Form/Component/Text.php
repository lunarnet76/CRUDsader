<?php
namespace Art\Form\Component {
    class Text extends \Art\Form\Component {
        public function __construct($value){
            $this->_value=$value;
        }
        public function error(){return false;}

        public function isEmpty(){return false;}

        public function receive($data=null){
            $this->_value=empty($data)?$this->_value:$data;
        }

        public function toHTML(){
            return '<input type="text" '.$this->getHTMLAttributes().' value="'.$this->_value.'">';
        }
    }
}