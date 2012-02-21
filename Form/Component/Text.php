<?php
namespace CRUDsader\Form\Component {
    class Text extends \CRUDsader\Form\Component {
         public function toHtml() {
            return '<textarea ' . $this->getHtmlAttributesToHtml() . '>'.(!\CRUDsader\Expression::isEmpty($this->_inputValue)?$this->_inputValue:'').'</textarea>';
        }
    }
}