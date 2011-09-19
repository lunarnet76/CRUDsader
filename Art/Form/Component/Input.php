<?php
namespace Art\Form\Component {
    class Input extends \Art\Form\Component {
        
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