<?php
namespace CRUDsader\Form\Component {
    class Hidden extends \CRUDsader\Form\Component {
        public function isEmpty(){return false;}

        public function toInput(){
            $this->_htmlAttributes['type']='hidden';
            return '<input '.$this->getHtmlAttributesToHtml().' value="'.(isset($this->_value)?$this->_value:'').'">';
        }
    }
}