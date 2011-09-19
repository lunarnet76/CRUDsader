<?php
namespace Art\Form\Component {
    class Composition extends \Art\Form\Component {
        public function error(){return false;}

        public function isEmpty(){
            return empty($this->_value);
        }

        public function receive($data=null){
            $this->_value=empty($data)?$this->_value:$data;
            $this->notify();
        }

        public function toHTML(){
            return '<select '.$this->getHTMLAttributes().' ><option>'.$this->_value.'</option></select>';
        }
    }
}