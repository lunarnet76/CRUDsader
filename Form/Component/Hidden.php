<?php
namespace Art\Form\Component {
    class Hidden extends \Art\Form\Component {
        public function inputEmpty(){return false;}

        public function toHTML(){
            return '<input type="hidden" '.$this->getHtmlAttributesToHtml().' value="'.$this->getInputValue().'">';
        }
    }
}