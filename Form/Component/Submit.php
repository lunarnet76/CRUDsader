<?php
namespace CRUDsader\Form\Component {
    class Submit extends \CRUDsader\Form\Component {
        protected $_text='ok';
        public function inputEmpty(){return false;}

        public function setText($text){
            $this->_text=$text;
        }
        public function toHTML(){
            $this->_htmlAttributes['type']='submit';
            $this->_htmlAttributes['class']='submit';
            return '<input  '.$this->getHtmlAttributesToHtml().' value="'.$this->_text.'"/>';
        }
    }
}