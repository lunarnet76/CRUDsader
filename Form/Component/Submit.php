<?php
namespace CRUDsader\Form\Component {
    class Submit extends \CRUDsader\Form\Component {
		protected $_parameters = array('isSubmit' => true);
        protected $_text='ok';

        public function setText($text){
            $this->_text=$text;
        }
        public function toInput(){
            $this->_htmlAttributes['type']='submit';
            if(!isset($this->_htmlAttributes['class']))
		    $this->_htmlAttributes['class']='submit';
            return '<input  '.$this->getHtmlAttributesToHtml().' value="'.$this->_text.'"/>';
        }
    }
}