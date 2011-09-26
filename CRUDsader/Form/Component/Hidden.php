<?php
namespace CRUDsader\Form\Component {
    class Hidden extends \CRUDsader\Form\Component {
        public function inputEmpty(){return false;}

        public function toHTML(){
            return '<input type="hidden" '.$this->getHtmlAttributesToHtml().' value="'.$this->getInputValue().'">';
        }
    }
}