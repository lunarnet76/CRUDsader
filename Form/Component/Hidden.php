<?php
namespace CRUDsader\Form\Component {
    class Hidden extends \CRUDsader\Form\Component {
        public function inputEmpty(){return false;}

        public function toHtml(){
            $this->_htmlAttributes['type']='hidden';
            return '<input '.$this->getHtmlAttributesToHtml().' value="'.(isset($this->_value)?$this->_value:'').'">';
        }
    }
}