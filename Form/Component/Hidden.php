<?php
namespace CRUDsader\Form\Component {
    class Hidden extends \CRUDsader\Form\Component {
        public function inputEmpty(){return false;}

        public function toHTML(){
            $this->_htmlAttributes['type']='hidden';
            return '<input '.$this->getHtmlAttributesToHtml().' value="'.(!\CRUDsader\Expression::isEmpty($this->_inputValue)?$this->_inputValue:'').'">';
        }
    }
}