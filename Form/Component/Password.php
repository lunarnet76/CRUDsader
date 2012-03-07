<?php
namespace CRUDsader\Form\Component {
    class Password extends \CRUDsader\Form\Component {
         public function toHtml() {
            return '<input type="password"' . $this->getHtmlAttributesToHtml() . ' value="' . (!isset($this->_value)?$this->_value:'') . '"/>';
        }
    }
}