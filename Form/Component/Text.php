<?php
namespace CRUDsader\Form\Component {
    class Text extends \CRUDsader\Form\Component {
         public function toHtml() {
            return '<textarea ' . $this->getHtmlAttributesToHtml() . '>'.(isset($this->_value)?$this->_value:'').'</textarea>';
        }
    }
}